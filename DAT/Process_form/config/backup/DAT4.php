<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../env/config.php');
require_once(__DIR__ . '/FormProcessor.php');

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => "Erro de conexão com o banco de dados: " . $conn->connect_error
    ]);
    exit;
}

// Instancia o processador de formulários
$processor = new FormProcessor($conn);

// Processa o formulário DAT4
$token = $_POST['token'] ?? null;

if (!$token) {
    echo json_encode([
        'success' => false,
        'message' => "Token não fornecido"
    ]);
    exit;
}

$result = $processor->process('DAT4', $token);

echo json_encode($result);
$conn->close();
?>