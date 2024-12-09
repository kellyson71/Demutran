<?php
include '../env/config.php';

// Cria a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura os dados do formulário
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $assunto = $_POST['assunto'];
    $mensagem = isset($_POST['mensagem']) ? $_POST['mensagem'] : null;

    // Prepara a query de inserção
    $sql = "INSERT INTO sac (nome, telefone, email, assunto, mensagem) VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro na preparação da declaração: " . $conn->error);
    }

    $stmt->bind_param("sssss", $nome, $telefone, $email, $assunto, $mensagem);

    if ($stmt->execute()) {
        // Salvar os valores originais do POST
        $original_post = $_POST;

        // Configurar os dados para envio de email
        $_POST = array(
            'email' => $email,
            'nome' => $nome,
            'assunto' => "Mensagem Recebida - DEMUTRAN",
            'mensagem' => "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='background-color: #f5f5f5; padding: 20px;'>
                    <h2 style='color: #2c5282;'>Mensagem Recebida</h2>
                    <p>Prezado(a) {$nome},</p>
                    <p>Agradecemos o seu contato! Sua mensagem foi recebida com sucesso.</p>
                    <hr style='border: 1px solid #e2e8f0;'>
                    <p><strong>Detalhes da sua mensagem:</strong></p>
                    <ul style='margin-left: 20px;'>
                        <li>Assunto: {$assunto}</li>
                        <li>Mensagem: {$mensagem}</li>
                    </ul>
                    <p><strong>Próximos Passos:</strong></p>
                    <ol style='margin-left: 20px;'>
                        <li>Nossa equipe irá analisar sua mensagem</li>
                        <li>Se necessário, entraremos em contato através dos dados fornecidos</li>
                        <li>O prazo de resposta é de até 5 dias úteis</li>
                    </ol>
                    <p><strong>IMPORTANTE:</strong></p>
                    <ul style='margin-left: 20px; color: #e53e3e;'>
                        <li>Este é um e-mail automático, não responda</li>
                        <li>Para novo contato, utilize nossos canais oficiais</li>
                    </ul>
                    <div style='background-color: #ffffff; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <p><strong>Canais de Atendimento DEMUTRAN:</strong></p>
                        <p>📞 Telefone: (84) 3351-2868</p>
                        <p>📧 E-mail: demutran@paudosferros.rn.gov.br</p>
                        <p>📍 Endereço: Av. Getúlio Vargas, 1323, Centro, Pau dos Ferros-RN</p>
                        <p>⏰ Horário de Atendimento: Segunda a Sexta, das 07h às 13h</p>
                    </div>
                </div>
            </body>
            </html>"
        );

        // Incluir e executar o envio de email
        try {
            require_once '../utils/mail.php';
            error_log("Enviando email para: " . $email);
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
        }

        // Restaurar os valores originais do POST
        $_POST = $original_post;

        echo "Mensagem enviada com sucesso!";
    } else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>