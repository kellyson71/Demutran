<?php
header('Content-Type: application/json');

// Recebe os dados do POST
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['gmail']) && isset($data['nome'])) {
    // Gera um token único
    $token = bin2hex(random_bytes(16));
    
    // Aqui você pode salvar o token no banco de dados se necessário
    
    // Retorna o token
    echo json_encode([
        'success' => true,
        'token' => $token
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Dados inválidos'
    ]);
}
