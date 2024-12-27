<?php
session_start();
include '../env/config.php';

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

// Receber e decodificar os dados JSON
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
    exit();
}

// Atualizar o status do usuário para administrador
$sql = "UPDATE usuarios SET is_admin = 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuário promovido a administrador com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário']);
}

$stmt->close();
$conn->close();
