<?php
session_start();
include '../env/config.php';

// Habilitar exibição de erros para depuração
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir o cabeçalho como JSON
header('Content-Type: application/json; charset=utf-8');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit();
}

// Obter os dados enviados na requisição
$data = json_decode(file_get_contents('php://input'), true);

// Sanitizar o ID do usuário
if (!isset($data['id']) || !filter_var($data['id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário inválido.']);
    exit();
}

$id = intval($data['id']);

// Obter dados do usuário pendente
$sql = "SELECT * FROM usuarios_pendentes WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conn->error]);
    exit();
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$usuarioPendente = $result->fetch_assoc();

if ($usuarioPendente) {
    // Verificar se o email já existe na tabela 'usuarios'
    $sqlCheck = "SELECT id FROM usuarios WHERE email = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    if (!$stmtCheck) {
        echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta de verificação: ' . $conn->error]);
        exit();
    }
    $stmtCheck->bind_param('s', $usuarioPendente['email']);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    if ($resultCheck->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Este email já está registrado.']);
        exit();
    }

    // Inserir na tabela 'usuarios'
    $sqlInsert = "INSERT INTO usuarios (nome, email, senha, data_registro) VALUES (?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    if (!$stmtInsert) {
        echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta de inserção: ' . $conn->error]);
        exit();
    }
    $stmtInsert->bind_param('ssss', $usuarioPendente['nome'], $usuarioPendente['email'], $usuarioPendente['senha'], $usuarioPendente['data_registro']);
    $stmtInsert->execute();

    if ($stmtInsert->affected_rows > 0) {
        // Remover da tabela 'usuarios_pendentes'
        $sqlDelete = "DELETE FROM usuarios_pendentes WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        if (!$stmtDelete) {
            echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta de exclusão: ' . $conn->error]);
            exit();
        }
        $stmtDelete->bind_param('i', $id);
        $stmtDelete->execute();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao inserir usuário na tabela principal: ' . $stmtInsert->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
}
?>