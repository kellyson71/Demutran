<?php
session_start();
include '../env/config.php';

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

// Receber dados do POST
$data = json_decode(file_get_contents('php://input'), true);
$nome = $data['nome'];
$email = $data['email'];
$senha = $data['senha'];
$is_admin = $data['is_admin'] ? 1 : 0;
$avatar_base64 = $data['avatar'] ?? null; // Receber avatar em base64

// Validações básicas
if (empty($nome) || empty($email) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit();
}

// Verificar se o email já existe
$check_email = "SELECT id FROM usuarios WHERE email = ?";
$stmt_check = $conn->prepare($check_email);
$stmt_check->bind_param('s', $email);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();

// Hash da senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Inserir usuário primeiro para obter o ID
$sql = "INSERT INTO usuarios (nome, email, senha, is_admin, data_registro) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssi', $nome, $email, $senha_hash, $is_admin);

if ($stmt->execute()) {
    $usuario_id = $conn->insert_id;
    $avatar_path = null;

    // Se houver avatar, processar o upload
    if ($avatar_base64) {
        // Criar diretório para o usuário se não existir
        $user_dir = "../avatar/user{$usuario_id}";
        if (!file_exists($user_dir)) {
            mkdir($user_dir, 0777, true);
        }

        // Decodificar e salvar a imagem
        $avatar_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $avatar_base64));
        $avatar_file = "{$user_dir}/avatar.png";
        file_put_contents($avatar_file, $avatar_data);

        // Atualizar o caminho do avatar no banco
        $avatar_path = "avatar/user{$usuario_id}/avatar.png";
        $sql_update = "UPDATE usuarios SET avatar_url = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('si', $avatar_path, $usuario_id);
        $stmt_update->execute();
        $stmt_update->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Usuário criado com sucesso',
        'usuario' => [
            'id' => $usuario_id,
            'nome' => $nome,
            'email' => $email,
            'is_admin' => $is_admin,
            'data_registro' => date('d/m/Y'),
            'avatar' => $avatar_path
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao criar usuário']);
}

$stmt->close();
$conn->close();
