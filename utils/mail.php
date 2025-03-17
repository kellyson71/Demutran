<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once(__DIR__ . '/../lib/vendor/autoload.php');

// Configuração do modo de teste
$TEST_MODE = true; // Mude para false quando quiser enviar emails reais

// Processar solicitações AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Verifica se é uma requisição JSON
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if (strpos($contentType, 'application/json') !== false) {
        // Recebe o conteúdo JSON e converte para array
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (isset($data['email']) && isset($data['nome']) && isset($data['assunto']) && isset($data['mensagem'])) {
            $result = sendMail($data['email'], $data['nome'], $data['assunto'], $data['mensagem']);
            echo json_encode(['success' => $result]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
            exit;
        }
    } elseif (isset($_POST['email']) && isset($_POST['nome']) && isset($_POST['assunto']) && isset($_POST['mensagem'])) {
        // Para compatibilidade com form-data
        $result = sendMail($_POST['email'], $_POST['nome'], $_POST['assunto'], $_POST['mensagem']);
        echo json_encode(['success' => $result]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit;
    }
}

function sendMail($email, $nome, $assunto, $mensagem)
{
    error_log("Função sendMail chamada para: $email");
    global $TEST_MODE;

    if ($TEST_MODE) {
        // Log das informações do email em modo de teste
        error_log("=== EMAIL EM MODO DE TESTE ===");
        error_log("Para: " . $email);
        error_log("Nome: " . $nome);
        error_log("Assunto: " . $assunto);
        error_log("Mensagem: " . $mensagem);
        error_log("============================");
        return true; // Simula envio bem-sucedido
    }

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
        // $mail->AddEmbeddedImage('./assets/icon.png', 'logo_demutran');

        $mail->Username = 'demutran@demutranpaudosferros.com.br';
        $mail->setFrom('demutran@demutranpaudosferros.com.br', 'demutran de Pau dos Ferros');
        $mail->Password = 'WRVGAxCbrJ8wdM$';
        $mail->Port = 465;

        $mail->addAddress($email, $nome);

        $mail->Subject = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
        $mail->isHTML(true);
        $mail->Body = mb_convert_encoding($mensagem, 'UTF-8', 'UTF-8');

        if (!$mail->send()) {
            error_log("Erro ao enviar email: " . $mail->ErrorInfo);
            return false;
        } else {
            error_log("Email enviado com sucesso para: " . $email);
            return true;
        }
    } catch (Exception $e) {
        error_log("Exceção ao enviar email: " . $e->getMessage());
        return false;
    }
}
?>