<?php
require_once(__DIR__ . '/../utils/mail.php');

// Dados do email de teste
$emailDestino = 'kellyson.medeiros.pdf@gmail.com';
$nomeDestino = 'Kellyson Medeiros';
$assunto = 'Teste de Envio de Email - DEMUTRAN';
$mensagem = '
<html>
<body>
    <h2>Teste de Envio de Email</h2>
    <p>Olá, este é um email de teste do sistema DEMUTRAN.</p>
    <p>Se você recebeu este email, significa que o sistema está funcionando corretamente.</p>
    <br>
    <p>Atenciosamente,<br>
    Equipe DEMUTRAN</p>
</body>
</html>';

// Tenta enviar o email
$resultado = sendMail($emailDestino, $nomeDestino, $assunto, $mensagem);

if ($resultado) {
    echo "Email enviado com sucesso!";
} else {
    echo "Erro ao enviar email. Verifique os logs para mais detalhes.";
}
