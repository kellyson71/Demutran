<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../env/config.php'); // Corrigindo o caminho para apontar para o config.php no diretório env

// Recebe os dados do POST
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['gmail']) && isset($data['nome'])) {
    try {
        // Verifica se a conexão com o banco está ativa
        if (!$conn || $conn->connect_error) {
            throw new Exception("Falha na conexão com o banco de dados: " . ($conn ? $conn->connect_error : "Conexão não estabelecida"));
        }

        // Gera um token único
        $token = bin2hex(random_bytes(16));

        // Obter o próximo ID para inserção (máximo ID atual + 1)
        $sql_max_id = "SELECT MAX(id) as max_id FROM formularios_dat_central";
        $result = $conn->query($sql_max_id);

        if (!$result) {
            throw new Exception("Erro ao obter o ID máximo: " . $conn->error);
        }

        $row = $result->fetch_assoc();
        $next_id = ($row['max_id'] ?? 0) + 1;

        // Prepara a inserção no banco de dados usando sintaxe mysqli
        $sql = "INSERT INTO formularios_dat_central (
            id,
            token, 
            email_usuario,
            status,
            preenchimento_status,
            tipo,
            is_read
        ) VALUES (?, ?, ?, 'Pendente', 'Incompleto', 'DAT', 0)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro na preparação da query: " . $conn->error);
        }

        $stmt->bind_param("iss", $next_id, $token, $data['gmail']);
        $result = $stmt->execute();

        if (!$result) {
            throw new Exception("Erro ao executar a query: " . $stmt->error);
        }

        // Verifica se a inserção foi bem-sucedida
        if ($stmt->affected_rows <= 0) {
            throw new Exception("Nenhum registro foi inserido");
        }

        // Retorna o token
        echo json_encode([
            'success' => true,
            'token' => $token
        ]);

        $stmt->close();
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