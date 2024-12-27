<?php
session_start();
include '../env/config.php';

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

// Receber dados do POST
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

// Validar ID
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
    exit();
}

// Não permitir que um usuário delete a si mesmo
if ($id == $_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'message' => 'Você não pode deletar seu próprio usuário']);
    exit();
}

// Deletar usuário
$sql = "DELETE FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuário deletado com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao deletar usuário']);
}

$stmt->close();
$conn->close();
