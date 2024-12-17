
<?php
header('Content-Type: application/json');
require_once '../env/config.php';

try {
    // Receber dados do POST
    $dados = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($dados['id']) || !isset($dados['tipo'])) {
        throw new Exception('Dados inválidos');
    }

    $id = $dados['id'];
    $tipo = $dados['tipo'];

    // Determinar tabela correta
    $tabela = match($tipo) {
        'SAC' => 'sac',
        'JARI' => 'solicitacoes_demutran',
        'PCD' => 'solicitacao_cartao',
        'DAT' => 'DAT1',
        'Parecer' => 'Parecer',
        default => null
    };

    if (!$tabela) {
        throw new Exception('Tipo de formulário inválido');
    }

    // Atualizar status no banco
    $sql = "UPDATE $tabela SET situacao = 'Concluído' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar status');
    }

    // Buscar dados do formulário para envio de email
    $sql = "SELECT * FROM $tabela WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if ($resultado) {
        // Incluir função de envio de email
        require_once 'detalhes_formulario.php';
        enviarEmailConclusao($tipo, $resultado);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}