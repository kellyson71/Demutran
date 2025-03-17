<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../env/config.php');
require_once(__DIR__ . '/FormProcessor.php');

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => "Erro de conexão com o banco de dados: " . $conn->connect_error
    ]);
    exit;
}

// Para DAT3, precisamos de um tratamento especial pelo formato JSON e tabelas relacionadas
$data = file_get_contents('php://input');
$requestData = json_decode($data, true);

if (!$requestData || !isset($requestData['token'])) {
    echo json_encode([
        'success' => false,
        'message' => "Dados inválidos ou token não fornecido"
    ]);
    exit;
}

$token = $requestData['token'];
$vehiclesData = $requestData['vehiclesData'] ?? [];
$totalVehicles = count($vehiclesData);

// Processo específico para DAT3 devido à estrutura complexa
$conn->begin_transaction();
try {
    // Verificar token
    $checkToken = $conn->prepare("SELECT id FROM formularios_dat_central WHERE token = ?");
    $checkToken->bind_param("s", $token);
    $checkToken->execute();
    $result = $checkToken->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Token inválido ou formulário não encontrado');
    }
    $checkToken->close();

    // Verificar registro existente
    $checkExisting = $conn->prepare("SELECT id FROM user_vehicles WHERE token = ?");
    $checkExisting->bind_param("s", $token);
    $checkExisting->execute();
    $existingResult = $checkExisting->get_result();

    if ($existingResult->num_rows > 0) {
        // Atualizar registro existente
        $userVehiclesId = $existingResult->fetch_object()->id;
        $updateVehicles = $conn->prepare("UPDATE user_vehicles SET total_vehicles = ? WHERE id = ?");
        $updateVehicles->bind_param("ii", $totalVehicles, $userVehiclesId);
        $updateVehicles->execute();

        // Remover registros antigos de danos
        $deleteOldDamages = $conn->prepare("DELETE FROM vehicle_damages WHERE user_vehicles_id = ?");
        $deleteOldDamages->bind_param("i", $userVehiclesId);
        $deleteOldDamages->execute();
    } else {
        // Criar novo registro
        $insertVehicles = $conn->prepare("INSERT INTO user_vehicles (token, total_vehicles) VALUES (?, ?)");
        $insertVehicles->bind_param("si", $token, $totalVehicles);
        $insertVehicles->execute();
        $userVehiclesId = $insertVehicles->insert_id;
    }

    // Inserir novos registros de danos
    $stmtDamages = $conn->prepare(
        "INSERT INTO vehicle_damages (
            user_vehicles_id, 
            dianteira_direita,
            dianteira_esquerda,
            lateral_direita,
            lateral_esquerda,
            traseira_direita,
            traseira_esquerda,
            has_load_damage,
            nota_fiscal,
            tipo_mercadoria,
            valor_total,
            estimativa_danos,
            has_insurance,
            seguradora
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($vehiclesData as $vehicle) {
        // Processar partes danificadas
        $dianteiraDireita = false;
        $dianteiraEsquerda = false;
        $lateralDireita = false;
        $lateralEsquerda = false;
        $traseiraDireita = false;
        $traseiraEsquerda = false;

        if ($vehicle['damageSystem'] && isset($vehicle['damagedParts'])) {
            foreach ($vehicle['damagedParts'] as $part) {
                if ($part['checked']) {
                    $partName = preg_replace('/parte_danificada_(.+)_\d+/', '$1', $part['name']);
                    switch ($partName) {
                        case 'dianteira_direita':
                            $dianteiraDireita = true;
                            break;
                        case 'dianteira_esquerda':
                            $dianteiraEsquerda = true;
                            break;
                        case 'lateral_direita':
                            $lateralDireita = true;
                            break;
                        case 'lateral_esquerda':
                            $lateralEsquerda = true;
                            break;
                        case 'traseira_direita':
                            $traseiraDireita = true;
                            break;
                        case 'traseira_esquerda':
                            $traseiraEsquerda = true;
                            break;
                    }
                }
            }
        }

        $loadDamage = $vehicle['loadDamage'] ? 1 : 0;
        $notaFiscal = isset($vehicle['notaFiscal']) ? $vehicle['notaFiscal'] : null;
        $tipoMercadoria = isset($vehicle['tipoMercadoria']) ? $vehicle['tipoMercadoria'] : null;
        $valorTotal = isset($vehicle['valorTotal']) ? $vehicle['valorTotal'] : null;
        $estimativaDanos = isset($vehicle['estimativaDanos']) ? $vehicle['estimativaDanos'] : null;
        $hasInsurance = isset($vehicle['hasInsurance']) ? ($vehicle['hasInsurance'] ? 1 : 0) : 0;
        $seguradora = isset($vehicle['seguradora']) ? $vehicle['seguradora'] : null;

        $stmtDamages->bind_param(
            'iiiiiiiissdsis',
            $userVehiclesId,
            $dianteiraDireita,
            $dianteiraEsquerda,
            $lateralDireita,
            $lateralEsquerda,
            $traseiraDireita,
            $traseiraEsquerda,
            $loadDamage,
            $notaFiscal,
            $tipoMercadoria,
            $valorTotal,
            $estimativaDanos,
            $hasInsurance,
            $seguradora
        );

        $stmtDamages->execute();
    }

    $stmtDamages->close();

    // Atualizar o status em formularios_dat_central
    $updateStatus = $conn->prepare("UPDATE formularios_dat_central SET preenchimento_status = 'Em Andamento' WHERE token = ?");
    $updateStatus->bind_param("s", $token);
    $updateStatus->execute();
    $updateStatus->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Dados ' . ($existingResult->num_rows > 0 ? 'atualizados' : 'salvos') . ' com sucesso'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>