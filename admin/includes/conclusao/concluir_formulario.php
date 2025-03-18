<?php
session_start();
require_once '../../../env/config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

// Obter dados do POST (formato JSON)
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$tipo = $data['tipo'] ?? null;

if (!$id || !$tipo) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit();
}

try {
    // Atualizar o status do formulário para "concluído"
    atualizarStatusFormulario($conn, $id, $tipo);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Formulário concluído com sucesso'
    ]);
    exit();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}

// Função para atualizar o status do formulário
function atualizarStatusFormulario($conn, $id, $tipo)
{
    $tableName = '';
    $statusField = '';
    $statusValue = '';

    switch ($tipo) {
        case 'JARI':
            $tableName = 'solicitacoes_demutran';
            break;
        case 'PCD':
            $tableName = 'solicitacao_cartao';
            break;
        case 'DAT':
            // Usar diretamente a tabela formularios_dat_central
            $tableName = 'formularios_dat_central';
            break;
        case 'SAC':
            $tableName = 'sac';
            break;
        case 'Parecer':
            $tableName = 'parecer';
            break;
        default:
            throw new Exception('Tipo de formulário inválido');
    }

    if (empty($tableName)) {
        throw new Exception('Não foi possível determinar a tabela do formulário');
    }

    // Verificar os possíveis campos de status
    $possiveisStatusFields = ['status', 'situacao', 'concluido', 'is_read'];

    foreach ($possiveisStatusFields as $field) {
        $checkFieldSql = "SHOW COLUMNS FROM $tableName LIKE '$field'";
        $checkResult = $conn->query($checkFieldSql);

        if ($checkResult && $checkResult->num_rows > 0) {
            $statusField = $field;

            // Definir o valor apropriado para o campo
            switch ($field) {
                case 'status':
                case 'situacao':
                    $statusValue = 'concluido';
                    break;
                case 'concluido':
                    $statusValue = '1';
                    break;
                case 'is_read':
                    $statusValue = 1; // valor numérico
                    break;
            }

            break; // Se encontrou um campo válido, para o loop
        }
    }

    if (empty($statusField)) {
        throw new Exception("Não foi possível encontrar um campo de status válido na tabela $tableName");
    }

    // Atualiza o status na tabela
    $sql = "UPDATE $tableName SET $statusField = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Verifica o tipo do valor para fazer o binding correto
    if (is_numeric($statusValue)) {
        $stmt->bind_param('ii', $statusValue, $id);
    } else {
        $stmt->bind_param('si', $statusValue, $id);
    }

    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar status do formulário: ' . $stmt->error);
    }

    // Registrar log da conclusão
    registrarLog($conn, $id, $tipo, $_SESSION['usuario_id']);

    return true;
}

// Função para registrar log da conclusão
function registrarLog($conn, $formularioId, $tipoFormulario, $usuarioId)
{
    $sql = "INSERT INTO logs_sistema (usuario_id, acao, tipo_formulario, formulario_id, data_hora) 
            VALUES (?, 'concluir', ?, ?, NOW())";

    $stmt = $conn->prepare($sql);

    // Verifica se a tabela existe
    $tableExistsSql = "SHOW TABLES LIKE 'logs_sistema'";
    $tableResult = $conn->query($tableExistsSql);

    // Se a tabela não existir, não registra o log e não gera erro
    if ($tableResult->num_rows > 0) {
        $stmt->bind_param('isi', $usuarioId, $tipoFormulario, $formularioId);
        $stmt->execute();
    }
}
