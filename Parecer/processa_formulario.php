<?php
session_start();
include '../env/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validar dados básicos
        $requiredFields = ['nome_solicitante', 'telefone', 'cpf_cnpj', 'email', 'local', 'evento', 'ponto_referencia', 'data_horario'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Campo $field é obrigatório");
            }
        }

        // Gerar protocolo
        $protocolo = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        // Inserir dados básicos
        $stmt = $conn->prepare("INSERT INTO Parecer (protocolo, nome, telefone, cpf_cnpj, email, local, evento, 
                               ponto_referencia, data_horario, declaracao) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $declaracao = isset($_POST['declaracao']) ? 1 : 0;
        $stmt->bind_param("sssssssssi", 
            $protocolo,
            $_POST['nome_solicitante'],
            $_POST['telefone'],
            $_POST['cpf_cnpj'],
            $_POST['email'],
            $_POST['local'],
            $_POST['evento'],
            $_POST['ponto_referencia'],
            $_POST['data_horario'],
            $declaracao
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao salvar dados: " . $stmt->error);
        }

        $lastId = $conn->insert_id;

        // Processar arquivos
        $uploadDir = './midia/' . $lastId . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Processar uploads
        $arquivos = [
            'doc_identificacao' => 'documento',
            'comp_residencia' => 'comprovante'
        ];

        $uploadedFiles = [];
        foreach ($arquivos as $input => $prefix) {
            if (!isset($_FILES[$input]) || $_FILES[$input]['error'] != 0) {
                throw new Exception("Erro no upload do arquivo $input");
            }

            $file = $_FILES[$input];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Validações
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                throw new Exception("Tipo de arquivo inválido para $input");
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("Arquivo $input muito grande");
            }

            $fileName = $prefix . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception("Erro ao mover arquivo $input");
            }

            $uploadedFiles[$input] = $fileName;
        }

        // Atualizar registro com arquivos
        $stmt = $conn->prepare("UPDATE Parecer SET documento_identificacao = ?, comprovante_residencia = ? WHERE id = ?");
        $stmt->bind_param("ssi", 
            $uploadedFiles['doc_identificacao'],
            $uploadedFiles['comp_residencia'],
            $lastId
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar arquivos: " . $stmt->error);
        }

        // Enviar email de confirmação
        $to = $_POST['email'];
        $subject = "Solicitação de Parecer - Protocolo: $protocolo";
        $message = "Sua solicitação foi recebida com sucesso!\n";
        $message .= "Protocolo: $protocolo\n";
        // ... resto do email ...

        // mail($to, $subject, $message); // Descomente quando configurar o email

        $_SESSION['success_message'] = "Solicitação enviada com sucesso! Seu protocolo é: $protocolo";
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }

    // Fechar conexões
    if (isset($stmt)) $stmt->close();
    $conn->close();

    // Redirecionar de volta
    header('Location: index.php');
    exit;
}

// Se não for POST, redirecionar
header('Location: index.php');