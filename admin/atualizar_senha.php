<?php
session_start();
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['success' => false, 'message' => 'Não autorizado']));
}

$data = json_decode(file_get_contents('php://input'), true);
$senha_atual = $data['senha_atual'];
$nova_senha = $data['nova_senha'];

// Verificar senha atual
$sql = "SELECT senha FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!password_verify($senha_atual, $usuario['senha'])) {
    die(json_encode(['success' => false, 'message' => 'Senha atual incorreta']));
}

// Atualizar senha
$nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
$sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nova_senha_hash, $_SESSION['usuario_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Senha atualizada com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar senha']);
}
?>