<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../env/config.php'); // Corrigindo o caminho para apontar para o config.php no diretório env

// Recebe os dados do POST
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['gmail']) && isset($data['nome'])) {
    try {
        // Gera um token único
        $token = bin2hex(random_bytes(16));

        // Prepara a inserção no banco de dados usando sintaxe mysqli
        $sql = "INSERT INTO formularios_dat_central (
            token, 
            email_usuario,
            status,
            preenchimento_status,
            tipo
        ) VALUES (?, ?, 'Pendente', 'Incompleto', 'DAT')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $token, $data['gmail']);
        $stmt->execute();

        // Retorna o token
        echo json_encode([
            'success' => true,
            'token' => $token
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao salvar no banco de dados: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Dados inválidos'
    ]);
}