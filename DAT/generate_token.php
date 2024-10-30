<?php
include './scr/config.php'; 

$data = json_decode(file_get_contents('php://input'), true);
$gmail = $data['gmail'] ?? null;

if (!$gmail || !filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Gmail inválido'
    ]);
    exit;
}

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Erro ao conectar ao banco de dados: ' . $conn->connect_error
    ]));
}

$token = bin2hex(random_bytes(16));

$sql = "INSERT INTO tokens (token, user_email) VALUES ('$token', '$gmail')";
if ($conn->query($sql) === TRUE) {
    echo json_encode([
        'success' => true,
        'token' => $token
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar o token: ' . $conn->error
    ]);
}

$conn->close();