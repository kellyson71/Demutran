<?php
session_start();
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Configurações simplificadas
$userDir = __DIR__ . '/avatar/user' . $_SESSION['usuario_id'];
$avatarPath = $userDir . '/avatar.png';
$relativePath = './avatar/user' . $_SESSION['usuario_id'] . '/avatar.png';

// Criar diretório do usuário se não existir
if (!file_exists($userDir)) {
    mkdir($userDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    try {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no upload do arquivo.');
        }

        // Converter e salvar imagem
        $image = imagecreatefromstring(file_get_contents($file['tmp_name']));
        if ($image) {
            // Redimensionar se necessário
            $width = imagesx($image);
            $height = imagesy($image);
            if ($width > 500 || $height > 500) {
                $ratio = min(500/$width, 500/$height);
                $new_width = $width * $ratio;
                $new_height = $height * $ratio;
                $new_image = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                $image = $new_image;
            }
            
            // Salvar como PNG
            imagepng($image, $avatarPath);
            imagedestroy($image);

            // Atualizar banco de dados - usando exatamente o caminho relativo que queremos
            $stmt = $conn->prepare("UPDATE usuarios SET avatar_url = ? WHERE id = ?");
            $stmt->bind_param("si", $relativePath, $_SESSION['usuario_id']);
            $stmt->execute();

            $_SESSION['usuario_avatar'] = $relativePath;
            echo json_encode(['success' => true, 'message' => 'Avatar atualizado com sucesso!']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}
?>