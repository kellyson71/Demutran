<?php
header('Content-Type: application/json');
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validação dos dados
if (!isset($data['email']) || !isset($data['nome']) || !isset($data['id']) || !isset($data['tipo'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Dados incompletos',
        'received' => $data
    ]);
    exit;
}

$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

try {
    // Template base do email
    $baseTemplate = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <div style='background-color: #f5f5f5; padding: 20px;'>
                <h2 style='color: #2c5282;'>%TITULO%</h2>
                <p>Prezado(a) {$data['nome']},</p>
                %CONTEUDO%
                <div style='background-color: #ffffff; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                    <p><strong>Canais de Atendimento DEMUTRAN:</strong></p>
                    <p>📞 Telefone: (84) 3351-2868</p>
                    <p>📧 E-mail: demutran@paudosferros.rn.gov.br</p>
                    <p>📍 Endereço: Av. Getúlio Vargas, 1323, Centro, Pau dos Ferros-RN</p>
                </div>
            </div>
        </body>
        </html>";

    // Conteúdo específico para cada tipo de solicitação
    switch ($data['tipo']) {
        case 'DAT':
            $titulo = "DAT - Declaração de Acidente de Trânsito Concluída";
            $conteudo = "
                <p>Sua Declaração de Acidente de Trânsito (DAT) foi processada com sucesso!</p>
                <p><strong>Próximos Passos:</strong></p>
                <ol style='margin-left: 20px;'>
                    <li>Compareça ao DEMUTRAN para retirar sua DAT</li>
                    <li>Traga seu documento de identificação original</li>
                    <li>Se possível, traga também o protocolo #{$data['id']}</li>
                </ol>";
            break;

        case 'PCD':
            $titulo = "Cartão de Estacionamento PCD - Solicitação Concluída";
            $conteudo = "
                <p>Sua solicitação do Cartão de Estacionamento PCD foi aprovada!</p>
                <p><strong>Próximos Passos:</strong></p>
                <ol style='margin-left: 20px;'>
                    <li>Compareça ao DEMUTRAN para retirar seu cartão</li>
                    <li>Traga os seguintes documentos:</li>
                    <ul style='margin-left: 40px;'>
                        <li>Documento de identificação original</li>
                        <li>Laudo médico original</li>
                        <li>Comprovante de residência</li>
                    </ul>
                </ol>";
            break;

        case 'JARI':
            $titulo = "Recurso JARI - Análise Concluída";
            $conteudo = "
                <p>Seu recurso JARI foi analisado e processado!</p>
                <p><strong>Próximos Passos:</strong></p>
                <ol style='margin-left: 20px;'>
                    <li>Compareça ao DEMUTRAN para receber o resultado</li>
                    <li>Traga seu documento de identificação</li>
                    <li>O prazo para recursos adicionais, se necessário, é de 30 dias</li>
                </ol>";
            break;

        case 'SAC':
            $titulo = "Atendimento SAC - Solicitação Processada";
            $conteudo = "
                <p>Sua solicitação ao SAC foi processada!</p>
                <p><strong>Informações:</strong></p>
                <ul style='margin-left: 20px;'>
                    <li>Protocolo: #{$data['id']}</li>
                    <li>Status: Concluído</li>
                </ul>
                <p>Caso necessite de informações adicionais, entre em contato com nossos canais de atendimento.</p>";
            break;

        case 'Parecer':
            $titulo = "Parecer Técnico - Documento Disponível";
            $conteudo = "
                <p>Seu Parecer Técnico foi elaborado e está disponível!</p>
                <p><strong>Instruções:</strong></p>
                <ol style='margin-left: 20px;'>
                    <li>O documento deve ser retirado pessoalmente no DEMUTRAN</li>
                    <li>Apresente documento de identificação e o protocolo #{$data['id']}</li>
                    <li>O parecer tem validade de 90 dias após a emissão</li>
                </ol>";
            break;

        default:
            $titulo = "Protocolo Concluído";
            $conteudo = "
                <p>Seu protocolo #{$data['id']} foi concluído com sucesso.</p>
                <p>Por favor, compareça ao DEMUTRAN para mais informações.</p>";
    }

    // Monta o email final
    $mensagemFinal = str_replace(
        ['%TITULO%', '%CONTEUDO%'],
        [$titulo, $conteudo],
        $baseTemplate
    );

    // Configura os dados para o mail.php
    $_POST = array(
        'email' => $email,
        'nome' => $data['nome'],
        'assunto' => $titulo,
        'mensagem' => $mensagemFinal
    );

    // Log para debug
    error_log("Tentando enviar email para: " . $email . " | Nome: " . $data['nome'] . " | ID: " . $data['id']);

    // Incluir e executar o envio de email
    require_once '../utils/mail.php';
    
    echo json_encode(['success' => true, 'message' => 'Email enviado com sucesso']);

} catch (Exception $e) {
    error_log("Erro ao enviar email: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao enviar email: ' . $e->getMessage(),
        'details' => [
            'error' => $e->getMessage(),
            'data' => $data
        ]
    ]);
}
?>