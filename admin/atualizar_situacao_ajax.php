<?php
session_start();
header('Content-Type: application/json');
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['tipo'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$id = $data['id'];
$tipo = $data['tipo'];

try {
    // Log inicial
    error_log("Iniciando atualização para tipo: $tipo, id: $id");
    
    // Determinar tabela correta
    $tabela = '';
    switch ($tipo) {
        case 'DAT':
            $tabela = 'DAT4';
            break;
        case 'SAC':
            $tabela = 'sac';
            break;
        case 'JARI':
            $tabela = 'solicitacoes_demutran';
            break;
        case 'PCD':
            $tabela = 'solicitacao_cartao';
            break;
        case 'Parecer':
            $tabela = 'Parecer';
            break;
        default:
            throw new Exception("Tipo de formulário inválido: $tipo");
    }

    error_log("Tabela selecionada: $tabela");

    // Primeiro, verificar se o registro existe e seu status atual
    $checkSql = "SELECT id, situacao FROM $tabela WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        throw new Exception("Erro ao preparar consulta: " . $conn->error);
    }
    
    $checkStmt->bind_param('i', $id);
    if (!$checkStmt->execute()) {
        throw new Exception("Erro ao executar consulta: " . $checkStmt->error);
    }
    
    $result = $checkStmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Registro não encontrado na tabela $tabela com ID $id");
    }

    $row = $result->fetch_assoc();
    error_log("Status atual do registro: " . ($row['situacao'] ?? 'não definido'));

    // Atualizar status - Modificar a lógica de verificação
    if ($row['situacao'] === 'Concluído') {
        // Se já estiver concluído, retornar sucesso mas com flag indicando que já estava concluído
        echo json_encode([
            'success' => true, 
            'message' => 'Protocolo já estava concluído',
            'alreadyCompleted' => true,
            'details' => [
                'tabela' => $tabela,
                'id' => $id,
                'tipo' => $tipo,
                'status' => 'Concluído'
            ]
        ]);
    } else {
        // Se não estiver concluído, atualizar
        $sql = "UPDATE $tabela SET situacao = 'Concluído' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar atualização: " . $conn->error);
        }
        
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar atualização: " . $stmt->error);
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Status atualizado com sucesso',
            'alreadyCompleted' => false,
            'details' => [
                'tabela' => $tabela,
                'id' => $id,
                'tipo' => $tipo
            ]
        ]);
    }

} catch (Exception $e) {
    error_log("Erro ao atualizar status: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'details' => [
            'tabela' => $tabela ?? 'não definida',
            'id' => $id,
            'tipo' => $tipo,
            'error' => $conn->error ?? 'sem erro mysql'
        ]
    ]);
}

$conn->close();
?>