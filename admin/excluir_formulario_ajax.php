<?php
session_start();
require_once '../env/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('Usuário não autenticado');
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id']) || !isset($data['tipo'])) {
        throw new Exception('Dados incompletos');
    }

    $id = $data['id'];
    $tipo = $data['tipo'];

    // Inicia uma transação para garantir a integridade dos dados
    $conn->begin_transaction();

    try {
        if ($tipo === 'DAT') {
            // Primeiro, busca o token na tabela central
            $sql_token = "SELECT token FROM formularios_dat_central WHERE id = ?";
            $stmt_token = $conn->prepare($sql_token);
            $stmt_token->bind_param('i', $id);
            $stmt_token->execute();
            $result = $stmt_token->get_result();
            $dat_data = $result->fetch_assoc();

            if (!$dat_data) {
                throw new Exception('Formulário DAT não encontrado');
            }

            $token = $dat_data['token'];

            // Log para debug
            error_log("Excluindo DAT com token: " . $token);

            // Mapeamento correto de tabelas e suas respectivas colunas de token
            $dat_tables = [
                'DAT1' => ['table' => 'DAT1', 'token_column' => 'token'],
                'DAT2' => ['table' => 'DAT2', 'token_column' => 'token'],
                'DAT4' => ['table' => 'DAT4', 'token_column' => 'token'],
                'user_vehicles' => ['table' => 'user_vehicles', 'token_column' => 'token'],
                'vehicle_damages' => ['table' => 'vehicle_damages', 'token_column' => null], // Esta será tratada separadamente
                'formularios_dat_central' => ['table' => 'formularios_dat_central', 'token_column' => 'token'] // Esta usa token e também ID
            ];

            // Primeiro, excluir os registros de vehicle_damages que estão relacionados aos user_vehicles
            $sql_vehicles = "SELECT id FROM user_vehicles WHERE token = ?";
            $stmt_vehicles = $conn->prepare($sql_vehicles);
            $stmt_vehicles->bind_param('s', $token);
            $stmt_vehicles->execute();
            $result_vehicles = $stmt_vehicles->get_result();

            while ($vehicle = $result_vehicles->fetch_assoc()) {
                $sql_damages = "DELETE FROM vehicle_damages WHERE user_vehicles_id = ?";
                $stmt_damages = $conn->prepare($sql_damages);
                $stmt_damages->bind_param('i', $vehicle['id']);
                $stmt_damages->execute();
                error_log("Excluindo damages do veículo ID: " . $vehicle['id']);
            }

            // Agora exclui os registros das outras tabelas
            foreach ($dat_tables as $table_name => $table_info) {
                if ($table_info['token_column'] === null) {
                    continue; // Pula vehicle_damages pois já foi tratada
                }

                // Exclui usando a coluna de token apropriada
                $sql = "DELETE FROM {$table_info['table']} WHERE {$table_info['token_column']} = ?";
                error_log("SQL para excluir {$table_info['table']}: $sql");

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    error_log("Erro ao preparar SQL para {$table_info['table']}: " . $conn->error);
                    continue;
                }

                $stmt->bind_param('s', $token);
                $stmt->execute();
                error_log("Excluídos da tabela {$table_info['table']}: " . $stmt->affected_rows);
            }

            // Por fim, exclui da tabela central pelo ID
            $sql = "DELETE FROM formularios_dat_central WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            error_log("Excluído da tabela formularios_dat_central: " . $stmt->affected_rows);
        } else {
            // Para outros tipos de formulário
            $tabela = match ($tipo) {
                'SAC' => 'sac',
                'JARI' => 'solicitacoes_demutran',
                'PCD' => 'solicitacao_cartao',
                'Parecer' => 'parecer',
                default => throw new Exception('Tipo de formulário inválido')
            };

            // Verifica se o registro existe
            $sql_check = "SELECT id FROM $tabela WHERE id = ?";
            $stmt_check = $conn->prepare($sql_check);

            if (!$stmt_check) {
                throw new Exception('Erro ao preparar consulta: ' . $conn->error);
            }
            
            $stmt_check->bind_param('i', $id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();

            if (!$result->fetch_assoc()) {
                throw new Exception("Registro não encontrado na tabela $tabela com ID $id");
            }

            // Executa a exclusão
            $sql = "DELETE FROM $tabela WHERE id = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception('Erro ao preparar exclusão: ' . $conn->error);
            }
            
            $stmt->bind_param('i', $id);

            if (!$stmt->execute()) {
                throw new Exception('Erro ao executar exclusão: ' . $stmt->error);
            }

            if ($stmt->affected_rows === 0) {
                throw new Exception('Nenhum registro foi excluído');
            }
        }

        // Registra a exclusão no log
        $usuario_id = $_SESSION['usuario_id'];
        $data_hora = date('Y-m-d H:i:s');
        $sql_log = "INSERT INTO log_acoes (usuario_id, acao, tipo_formulario, formulario_id, data_hora) 
                    VALUES (?, 'Excluiu', ?, ?, ?)";
        $stmt_log = $conn->prepare($sql_log);

        if (!$stmt_log) {
            throw new Exception('Erro ao preparar log: ' . $conn->error);
        }
        
        $stmt_log->bind_param('isss', $usuario_id, $tipo, $id, $data_hora);

        if (!$stmt_log->execute()) {
            throw new Exception('Erro ao registrar log: ' . $stmt_log->error);
        }

        // Commit da transação
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Formulário excluído com sucesso'
        ]);
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Erro na exclusão: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir formulário: ' . $e->getMessage()
    ]);
}
?>