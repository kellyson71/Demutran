<?php
session_start();
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['success' => false, 'message' => 'Não autorizado']));
}

$data = json_decode(file_get_contents('php://input'), true);
$nome = trim($data['nome']);
$email = trim($data['email']);

if (empty($nome) || empty($email)) {
    die(json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']));
}

$sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $nome, $email, $_SESSION['usuario_id']);

if ($stmt->execute()) {
    $_SESSION['usuario_nome'] = $nome;
    echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar perfil']);
}
?>