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
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "ssl";
            
            $mail->Username = 'test@potocolo.estagiopaudosferros.com';
            $mail->setFrom('test@potocolo.estagiopaudosferros.com', 'Prefeitura de Pau dos Ferros');
            $mail->Password = 'Teste123!';
            $mail->Port = 465;
            
            $mail->addAddress($email, $nome);
            
            $mail->Subject = $assunto;
            $mail->Body    = $mensagem;
            
            if (!$mail->send()) {
                echo 'O e-mail não foi enviado.';
                echo 'Erro: ' . $mail->ErrorInfo;
            } else {
                echo 'O e-mail foi enviado com sucesso.';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
    
    sendMail($email, $nome, $assunto, $mensagem);
}
?>