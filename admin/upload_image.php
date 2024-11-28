<?php
session_start();
include '../env/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('Não autorizado');
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Nenhum arquivo enviado ou erro no upload');
    }

    $fileName = $_FILES['file']['name'];
    $fileType = $_FILES['file']['type'];
    $tmpName = $_FILES['file']['tmp_name'];
    
    // Verificar tipo de arquivo
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($fileType, $allowed)) {
        throw new Exception('Tipo de arquivo não permitido');
    }
    
    // Verificar tamanho (5MB máximo)
    if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
        throw new Exception('Arquivo muito grande (máximo 5MB)');
    }
    
    // Gerar nome único
    $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $fileName);
    
    // Criar pasta se não existir
    $uploadDir = './midia/editor/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Erro ao criar diretório');
        }
    }
    
    $targetPath = $uploadDir . $newFileName;
    
    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new Exception('Erro ao mover arquivo');
    }
    
    // Retornar URL relativa
    echo json_encode([
        'location' => $uploadDir . $newFileName
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}