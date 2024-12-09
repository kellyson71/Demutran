<?php
include '../../env/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    
    // Adicionar log para debug
    error_log("Iniciando processamento para token: " . $token);

    // Buscar informações do usuário usando o token
    $stmt = $conn->prepare("SELECT d1.email, d1.nome FROM DAT1 d1 WHERE d1.token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();

    if ($userData) {
        $email = $userData['email'];
        $nome = $userData['nome'];
        
        error_log("Dados encontrados - Email: " . $email . ", Nome: " . $nome);

        // Configurar os dados para envio de email
        $_POST = array(
            'email' => $email,
            'nome' => $nome,
            'assunto' => "Declaração de Acidente de Trânsito - DAT - Confirmação",
            'mensagem' => "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='background-color: #f5f5f5; padding: 20px;'>
                    <div style='background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                        <h2 style='color: #2c5282; margin-bottom: 20px;'>Declaração de Acidente de Trânsito - DAT</h2>
                        <p style='color: #4a5568;'>Prezado(a) {$nome},</p>
                        <p style='color: #4a5568;'>Sua Declaração de Acidente de Trânsito (DAT) foi registrada com sucesso em nosso sistema!</p>
                        
                        <div style='background-color: #ebf8ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                            <p style='color: #2b6cb0; margin: 0;'><strong>Seu Token de Acompanhamento:</strong></p>
                            <p style='color: #2b6cb0; font-size: 18px; margin: 10px 0;'><strong>{$token}</strong></p>
                        </div>

                        <div style='margin-top: 20px;'>
                            <h3 style='color: #2d3748;'>Próximos Passos:</h3>
                            <ol style='color: #4a5568;'>
                                <li style='margin-bottom: 10px;'>Sua declaração será analisada pela equipe técnica do DEMUTRAN</li>
                                <li style='margin-bottom: 10px;'>Você receberá um e-mail quando a análise for concluída</li>
                                <li style='margin-bottom: 10px;'>Em caso de dúvidas, apresente seu token de acompanhamento</li>
                            </ol>
                        </div>

                        <div style='background-color: #fff5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                            <p style='color: #c53030; margin: 0;'><strong>IMPORTANTE:</strong></p>
                            <ul style='color: #c53030; margin: 10px 0;'>
                                <li>Este é um e-mail automático, não responda</li>
                                <li>Guarde seu token de acompanhamento para consultas futuras</li>
                            </ul>
                        </div>

                        <div style='background-color: #f7fafc; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                            <p style='color: #2d3748; margin: 0;'><strong>Canais de Atendimento DEMUTRAN:</strong></p>
                            <p style='color: #4a5568; margin: 5px 0;'>📞 Telefone: (84) 3351-2868</p>
                            <p style='color: #4a5568; margin: 5px 0;'>📧 E-mail: demutran@paudosferros.rn.gov.br</p>
                            <p style='color: #4a5568; margin: 5px 0;'>📍 Endereço: Av. Getúlio Vargas, 1323, Centro, Pau dos Ferros-RN</p>
                            <p style='color: #4a5568; margin: 5px 0;'>⏰ Horário de Atendimento: Segunda a Sexta, das 07h às 13h</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>"
        );

        // Adicionar log antes de enviar o email
        error_log("Tentando enviar email para: " . $email);

        // Incluir e executar o envio de email
        require_once '../../utils/mail.php';
        
        // Adicionar log após tentativa de envio
        error_log("Tentativa de envio de email concluída");
        
        echo "E-mail enviado com sucesso";
    } else {
        error_log("Usuário não encontrado para o token: " . $token);
        echo "Usuário não encontrado";
    }
} else {
    error_log("Método de requisição inválido");
    echo "Método de requisição inválido";
}
?>