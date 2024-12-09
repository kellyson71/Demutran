<?php
// Inclui o arquivo do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

// Corrigir o caminho do autoload.php
require '../lib/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['email']) || !isset($_POST['nome']) || !isset($_POST['assunto']) || !isset($_POST['mensagem'])) {
        echo 'BAD REQUEST';
        exit;
    }
    
    $email = $_POST['email'];
    $nome = $_POST['nome'];
    $assunto = $_POST['assunto'];
    $mensagem = $_POST['mensagem'];
    
    function sendMail($email, $nome, $assunto, $mensagem) {
        try {
            error_log("Iniciando envio de email para: " . $email);
            
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "ssl";
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            
            // Adicionar imagem embutida
            $mail->AddEmbeddedImage('./assets/icon.png', 'logo_demutran');
            
            $mail->Username = 'test@potocolo.estagiopaudosferros.com';
            $mail->setFrom('test@potocolo.estagiopaudosferros.com', 'Prefeitura de Pau dos Ferros');
            $mail->Password = 'Teste123!';
            $mail->Port = 465;
            
            $mail->addAddress($email, $nome);
            
            $mail->Subject = '=?UTF-8?B?'.base64_encode($assunto).'?=';
            $mail->isHTML(true);
            $mail->Body = mb_convert_encoding($mensagem, 'UTF-8', 'UTF-8');
            
            if (!$mail->send()) {
                error_log("Erro ao enviar email: " . $mail->ErrorInfo);
                echo json_encode(['error' => true, 'message' => 'Erro ao enviar email: ' . $mail->ErrorInfo]);
            } else {
                error_log("Email enviado com sucesso para: " . $email);
            }
        } catch (Exception $e) {
            error_log("Exceção ao enviar email: " . $e->getMessage());
            echo json_encode(['error' => true, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    sendMail($email, $nome, $assunto, $mensagem);
}
?>