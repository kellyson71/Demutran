<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
include '../env/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido.']);
    exit();
}

$id = intval($data['id']);

// Remover da tabela 'usuarios_pendentes'
$sqlDelete = "DELETE FROM usuarios_pendentes WHERE id = ?";
$stmtDelete = $conn->prepare($sqlDelete);
$stmtDelete->bind_param('i', $id);
$stmtDelete->execute();

if ($stmtDelete->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir usuário pendente.']);
}
?>