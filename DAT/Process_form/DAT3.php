<?php
// Inclui o arquivo de configuração que contém as credenciais do banco de dados
include '../scr/config.php';

// Configura o cabeçalho para retornar um JSON como resposta
header('Content-Type: application/json');

// Captura os dados enviados via POST em formato JSON
$data = file_get_contents('php://input');

// Decodifica o JSON em um array associativo
$requestData = json_decode($data, true);

// Array para armazenar a resposta
$response = [];

// Verifica se há dados recebidos
if ($requestData) {
    $vehiclesData = $requestData['vehiclesData'];
    $token = $requestData['token'];

    // Adiciona o token à resposta
    $response['token'] = $token;

    // A conexão já foi feita no arquivo config.php, então você pode usar $conn diretamente

    // Itera sobre os veículos e insere cada um no banco de dados
    foreach ($vehiclesData as $vehicle) {
        $damageSystem = $vehicle['damageSystem'] ? 1 : 0;  // Sistema danificado (1 = Sim, 0 = Não)
        $damagedParts = json_encode($vehicle['damagedParts']);  // Armazena peças danificadas como JSON
        $loadDamage = $vehicle['loadDamage'] ? 1 : 0;  // Dano à carga (1 = Sim, 0 = Não)
        $notaFiscal = isset($vehicle['notaFiscal']) ? $vehicle['notaFiscal'] : null;
        $tipoMercadoria = isset($vehicle['tipoMercadoria']) ? $vehicle['tipoMercadoria'] : null;
        $valorTotal = isset($vehicle['valorTotal']) ? $vehicle['valorTotal'] : null;
        $estimativaDanos = isset($vehicle['estimativaDanos']) ? $vehicle['estimativaDanos'] : null;
        $hasInsurance = isset($vehicle['hasInsurance']) ? ($vehicle['hasInsurance'] ? 1 : 0) : null;
        $seguradora = isset($vehicle['seguradora']) ? $vehicle['seguradora'] : null;

        $stmt = $conn->prepare("INSERT INTO vehicles (token, damage_system, damaged_parts, load_damage, nota_fiscal, tipo_mercadoria, valor_total, estimativa_danos, has_insurance, seguradora) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param('sisssssdds', $token, $damageSystem, $damagedParts, $loadDamage, $notaFiscal, $tipoMercadoria, $valorTotal, $estimativaDanos, $hasInsurance, $seguradora);

        if ($stmt->execute()) {
            $response['vehicles'][] = ['message' => 'Veículo adicionado com sucesso!', 'vehicle_id' => $stmt->insert_id];
        } else {
            $response['vehicles'][] = ['error' => 'Erro ao adicionar veículo: ' . $stmt->error];
        }

        $stmt->close();
    }

    $conn->close();

    echo json_encode($response);
} else {
    echo json_encode(['error' => 'Nenhum dado recebido!']);
}
?>