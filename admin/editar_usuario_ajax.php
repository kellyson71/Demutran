<?php
session_start();
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['nome']) && isset($data['email'])) {
    $id = $data['id'];
    $nome = $data['nome'];
    $email = $data['email'];
    
    $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nome, $email, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
}