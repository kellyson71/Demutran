<?php
require_once(__DIR__ . '/../../env/config.php');
header('Content-Type: application/json');

$data = file_get_contents('php://input');
$requestData = json_decode($data, true);
$response = [];

if ($requestData) {
    $vehiclesData = $requestData['vehiclesData'];
    $token = $requestData['token'];
    $totalVehicles = count($vehiclesData);

    $conn->begin_transaction();

    try {
        // Buscar o ID do formulário central
        $stmt_form = $conn->prepare("SELECT id FROM formularios_dat_central WHERE token = ?");
        $stmt_form->bind_param("s", $token);
        $stmt_form->execute();
        $result_form = $stmt_form->get_result();
        $formulario_id = $result_form->fetch_object()->id;
        $stmt_form->close();

        // Inserir registro principal do usuário com formulario_id
        $stmt = $conn->prepare("INSERT INTO user_vehicles (token, total_vehicles, formulario_id) VALUES (?, ?, ?)");
        $stmt->bind_param('sii', $token, $totalVehicles, $formulario_id);

        if ($stmt->execute()) {
            $userVehiclesId = $stmt->insert_id;
            $stmt->close();

            // Inserir dados de cada veículo incluindo formulario_id
            $stmtDamages = $conn->prepare("
                INSERT INTO vehicle_damages (
                    user_vehicles_id, 
                    vehicle_index,
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
                    seguradora,
                    formulario_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($vehiclesData as $index => $vehicle) {
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
                            $partName = str_replace(['parte_danificada_', '_' . ($index + 1)], '', $part['name']);
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

                $vehicleIndex = $index + 1;
                $loadDamage = $vehicle['loadDamage'] ? 1 : 0;
                $notaFiscal = isset($vehicle['notaFiscal']) ? $vehicle['notaFiscal'] : null;
                $tipoMercadoria = isset($vehicle['tipoMercadoria']) ? $vehicle['tipoMercadoria'] : null;
                $valorTotal = isset($vehicle['valorTotal']) ? $vehicle['valorTotal'] : null;
                $estimativaDanos = isset($vehicle['estimativaDanos']) ? $vehicle['estimativaDanos'] : null;
                $hasInsurance = isset($vehicle['hasInsurance']) ? ($vehicle['hasInsurance'] ? 1 : 0) : 0;
                $seguradora = isset($vehicle['seguradora']) ? $vehicle['seguradora'] : null;

                $stmtDamages->bind_param(
                    'iiiiiiiiissddsii',
                    $userVehiclesId,
                    $vehicleIndex,
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
                    $seguradora,
                    $formulario_id
                );

                $stmtDamages->execute();
            }

            $stmtDamages->close();
            $conn->commit();

            $response['success'] = true;
            $response['message'] = 'Dados salvos com sucesso';
            $response['user_vehicles_id'] = $userVehiclesId;
            
        } else {
            throw new Exception("Erro ao inserir registro principal");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response['success'] = false;
        $response['error'] = 'Erro ao processar dados: ' . $e->getMessage();
    }
    
    $conn->close();
    
} else {
    $response['success'] = false;
    $response['error'] = 'Nenhum dado recebido';
}

echo json_encode($response);
?>