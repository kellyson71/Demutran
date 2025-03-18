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
    // Buscar histórico de ações do formulário
    $historico = obterHistoricoAcoes($conn, $id, $tipo);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'historico' => $historico
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

// Função para obter o histórico de ações
function obterHistoricoAcoes($conn, $id, $tipo)
{
    // Verifica se a tabela existe
    $tableExistsSql = "SHOW TABLES LIKE 'logs_sistema'";
    $tableResult = $conn->query($tableExistsSql);

    if ($tableResult->num_rows == 0) {
        return [];
    }

    // Buscar logs relacionados ao formulário
    $sql = "SELECT l.*, u.nome as usuario_nome 
            FROM logs_sistema l
            LEFT JOIN usuarios u ON l.usuario_id = u.id
            WHERE l.formulario_id = ? AND l.tipo_formulario = ?
            ORDER BY l.data_hora DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $id, $tipo);
    $stmt->execute();
    $result = $stmt->get_result();

    $historico = [];
    while ($row = $result->fetch_assoc()) {
        // Formatar a data
        $dataHora = new DateTime($row['data_hora']);
        $row['data_formatada'] = $dataHora->format('d/m/Y H:i:s');

        // Adicionar descrição amigável da ação
        $row['acao_descricao'] = descricaoAcao($row['acao']);

        $historico[] = $row;
    }

    return $historico;
}

// Função para obter descrição amigável da ação
function descricaoAcao($acao)
{
    switch ($acao) {
        case 'visualizar':
            return 'Visualização do formulário';
        case 'editar':
            return 'Edição do formulário';
        case 'concluir':
            return 'Conclusão do formulário';
        case 'excluir':
            return 'Exclusão do formulário';
        default:
            return ucfirst($acao);
    }
}
