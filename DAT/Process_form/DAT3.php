<?php
require_once(__DIR__ . '/../../env/config.php');
header('Content-Type: application/json');

try {
    // Verificar método da requisição
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido. Use POST para enviar dados.', 405);
    }

    // Verificar conexão com o banco
    if ($conn->connect_error) {
        throw new Exception('Erro de conexão com o banco de dados: ' . $conn->connect_error, 500);
    }

    // Ler dados do corpo da requisição
    $data = file_get_contents('php://input');
    if (empty($data)) {
        throw new Exception('Dados não recebidos', 400);
    }

    $requestData = json_decode($data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao decodificar JSON: ' . json_last_error_msg(), 400);
    }

    // Validar dados recebidos
    if (!isset($requestData['token']) || empty($requestData['token'])) {
        throw new Exception('Token não fornecido', 400);
    }

    if (!isset($requestData['vehiclesData']) || !is_array($requestData['vehiclesData']) || empty($requestData['vehiclesData'])) {
        throw new Exception('Dados de veículos não fornecidos ou inválidos', 400);
    }

    $vehiclesData = $requestData['vehiclesData'];
    $token = $requestData['token'];
    $totalVehicles = count($vehiclesData);

    // Iniciar transação para garantir consistência dos dados
    $conn->begin_transaction();

    // Verifica se o token existe em formularios_dat_central
    $checkToken = $conn->prepare("SELECT id FROM formularios_dat_central WHERE token = ?");
    if (!$checkToken) {
        throw new Exception('Erro ao preparar consulta: ' . $conn->error, 500);
    }

    $checkToken->bind_param("s", $token);
    if (!$checkToken->execute()) {
        throw new Exception('Erro ao executar consulta: ' . $checkToken->error, 500);
    }

    $result = $checkToken->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Token inválido ou formulário não encontrado', 404);
    }
    $checkToken->close();

    // Verifica se já existe registro para este token
    $checkExisting = $conn->prepare("SELECT id FROM user_vehicles WHERE token = ?");
    if (!$checkExisting) {
        throw new Exception('Erro ao preparar consulta: ' . $conn->error, 500);
    }

    $checkExisting->bind_param("s", $token);
    if (!$checkExisting->execute()) {
        throw new Exception('Erro ao executar consulta: ' . $checkExisting->error, 500);
    }

    $existingResult = $checkExisting->get_result();
    $isUpdate = $existingResult->num_rows > 0;

    if ($isUpdate) {
        // Atualizar registro existente
        $userVehiclesId = $existingResult->fetch_object()->id;

        // Atualiza total_vehicles
        $updateVehicles = $conn->prepare("UPDATE user_vehicles SET total_vehicles = ? WHERE id = ?");
        if (!$updateVehicles) {
            throw new Exception('Erro ao preparar atualização: ' . $conn->error, 500);
        }

        $updateVehicles->bind_param("ii", $totalVehicles, $userVehiclesId);
        if (!$updateVehicles->execute()) {
            throw new Exception('Erro ao atualizar dados de veículos: ' . $updateVehicles->error, 500);
        }

        // Remove registros antigos de danos
        $deleteOldDamages = $conn->prepare("DELETE FROM vehicle_damages WHERE user_vehicles_id = ?");
        if (!$deleteOldDamages) {
            throw new Exception('Erro ao preparar exclusão: ' . $conn->error, 500);
        }

        $deleteOldDamages->bind_param("i", $userVehiclesId);
        if (!$deleteOldDamages->execute()) {
            throw new Exception('Erro ao excluir registros antigos: ' . $deleteOldDamages->error, 500);
        }
    } else {
        // Criar novo registro
        $insertVehicles = $conn->prepare("INSERT INTO user_vehicles (token, total_vehicles) VALUES (?, ?)");
        if (!$insertVehicles) {
            throw new Exception('Erro ao preparar inserção: ' . $conn->error, 500);
        }

        $insertVehicles->bind_param("si", $token, $totalVehicles);
        if (!$insertVehicles->execute()) {
            throw new Exception('Erro ao inserir dados de veículos: ' . $insertVehicles->error, 500);
        }

        // Obter o ID do registro inserido
        $userVehiclesId = $insertVehicles->insert_id;

        // Se insert_id falhou, tentar obter o ID diretamente do banco
        if (!$userVehiclesId) {
            $getInsertedId = $conn->prepare("SELECT id FROM user_vehicles WHERE token = ? ORDER BY id DESC LIMIT 1");
            if (!$getInsertedId) {
                throw new Exception('Erro ao preparar consulta para obter ID: ' . $conn->error, 500);
            }

            $getInsertedId->bind_param("s", $token);
            if (!$getInsertedId->execute()) {
                throw new Exception('Erro ao executar consulta para obter ID: ' . $getInsertedId->error, 500);
            }

            $idResult = $getInsertedId->get_result();
            if ($idResult->num_rows === 0) {
                throw new Exception('Registro inserido não encontrado', 500);
            }

            $userVehiclesId = $idResult->fetch_object()->id;
            $getInsertedId->close();
        }

        if (!$userVehiclesId || !is_numeric($userVehiclesId) || $userVehiclesId <= 0) {
            throw new Exception('ID do registro inserido é inválido: ' . var_export($userVehiclesId, true), 500);
        }

        // Log para depuração
        error_log("DAT3 - ID obtido após inserção: " . $userVehiclesId);
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
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    "
    );

    if (!$stmtDamages) {
        throw new Exception('Erro ao preparar inserção de danos: ' . $conn->error, 500);
    }

    $insertedDamages = 0;
    foreach ($vehiclesData as $vehicle) {
        // Validar dados do veículo
        if (!isset($vehicle['damageSystem'])) {
            continue; // Pular se não tiver informação de danos
        }

        // Processar partes danificadas
        $dianteiraDireita = false;
        $dianteiraEsquerda = false;
        $lateralDireita = false;
        $lateralEsquerda = false;
        $traseiraDireita = false;
        $traseiraEsquerda = false;

        if ($vehicle['damageSystem'] && isset($vehicle['damagedParts']) && is_array($vehicle['damagedParts'])) {
            foreach ($vehicle['damagedParts'] as $part) {
                if (isset($part['checked']) && $part['checked'] && isset($part['name'])) {
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

        $loadDamage = isset($vehicle['loadDamage']) && $vehicle['loadDamage'] ? 1 : 0;
        $notaFiscal = isset($vehicle['notaFiscal']) ? $vehicle['notaFiscal'] : null;
        $tipoMercadoria = isset($vehicle['tipoMercadoria']) ? $vehicle['tipoMercadoria'] : null;
        $valorTotal = isset($vehicle['valorTotal']) && is_numeric($vehicle['valorTotal']) ? $vehicle['valorTotal'] : null;
        $estimativaDanos = isset($vehicle['estimativaDanos']) ? $vehicle['estimativaDanos'] : null;
        $hasInsurance = isset($vehicle['hasInsurance']) && $vehicle['hasInsurance'] ? 1 : 0;
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

        if (!$stmtDamages->execute()) {
            throw new Exception('Erro ao inserir dados de danos: ' . $stmtDamages->error, 500);
        }
        $insertedDamages++;
    }

    // Verificar se todos os danos foram inseridos
    if ($insertedDamages === 0) {
        throw new Exception('Nenhum dado de danos foi inserido', 500);
    }

    // Atualiza o status de preenchimento no formularios_dat_central
    $updateStatus = $conn->prepare("UPDATE formularios_dat_central SET preenchimento_status = 'Em Andamento', ultima_atualizacao = CURRENT_TIMESTAMP WHERE token = ?");
    if (!$updateStatus) {
        throw new Exception('Erro ao preparar atualização de status: ' . $conn->error, 500);
    }

    $updateStatus->bind_param("s", $token);
    if (!$updateStatus->execute()) {
        throw new Exception('Erro ao atualizar status: ' . $updateStatus->error, 500);
    }

    // Commit da transação
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Dados ' . ($isUpdate ? 'atualizados' : 'salvos') . ' com sucesso',
        'token' => $token,
        'vehiclesProcessed' => $totalVehicles,
        'damagesInserted' => $insertedDamages
    ]);
} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($conn) && !$conn->connect_error) {
        $conn->rollback();
    }

    // Log do erro para facilitar a depuração
    error_log("Erro DAT3: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} finally {
    // Fechar as conexões abertas
    if (isset($stmtDamages)) $stmtDamages->close();
    if (isset($checkExisting)) $checkExisting->close();
    if (isset($getInsertedId)) $getInsertedId->close();
    if (isset($conn) && !$conn->connect_error) $conn->close();
}
?>