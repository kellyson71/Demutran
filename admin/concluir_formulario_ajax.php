<?php
session_start(); // Adicionar session_start aqui também pois é um ponto de entrada
// Garantir que erros PHP sejam tratados como JSON

function exception_error_handler($severity, $message, $file, $line)
{
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

// Capturar erros de parsing/syntax
ob_start();

// Remove session_start pois será incluído via config.php
header('Content-Type: application/json');
require_once '../env/config.php';

try {
    if (ob_get_length()) ob_clean();

    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('Usuário não autenticado');
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id']) || !isset($data['tipo'])) {
        throw new Exception('Dados incompletos');
    }

    $id = $data['id'];
    $tipo = $data['tipo'];

    // Determinar tabela correta baseado no tipo
    $tabela = match($tipo) {
        'SAC' => 'sac',
        'JARI' => 'solicitacoes_demutran',
        'PCD' => 'solicitacao_cartao',
        'DAT' => 'DAT1',
        'Parecer' => 'Parecer',
        default => throw new Exception('Tipo de formulário inválido')
    };

    // Primeiro buscar os dados antes de atualizar o status
    $sql_dados = "SELECT * FROM $tabela WHERE id = ?";
    $stmt_dados = $conn->prepare($sql_dados);
    $stmt_dados->bind_param('i', $id);
    $stmt_dados->execute();
    $dados = $stmt_dados->get_result()->fetch_assoc();

    if (!$dados) {
        throw new Exception('Dados do formulário não encontrados');
    }

    // Preparar e enviar o email primeiro
    $emailData = [
        'id' => $id,
        'tipo' => $tipo,
        'email' => $dados['email'] ?? '',
        'nome' => $dados['nome'] ?? '',
        'status' => 'Concluído'
    ];

    // Remover a chamada anterior do processar_email e substituir por:
    require_once '../utils/mail.php';

    // Antes do template de email, adicionar a verificação do tipo específico de recurso
    $tipoRecursoEspecifico = '';
    if ($tipo === 'JARI') {
        // Verificar o tipo específico do recurso
        $sql_tipo = "SELECT tipo_solicitacao FROM solicitacoes_demutran WHERE id = ?";
        $stmt_tipo = $conn->prepare($sql_tipo);
        $stmt_tipo->bind_param('i', $id);
        $stmt_tipo->execute();
        $result_tipo = $stmt_tipo->get_result();
        $tipo_dados = $result_tipo->fetch_assoc();

        switch ($tipo_dados['tipo_solicitacao'] ?? '') {
            case 'defesa_previa':
                $tipoRecursoEspecifico = "Defesa Prévia";
                break;
            case 'jari':
                $tipoRecursoEspecifico = "Recurso JARI";
                break;
            case 'apresentacao_condutor':
                $tipoRecursoEspecifico = "Apresentação de Condutor";
                break;
            default:
                $tipoRecursoEspecifico = "Formulário de Defesa";
        }
    }

    // Substituir a parte do template de email por:
    $baseTemplate = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <div style='background-color: #f5f5f5; padding: 20px;'>
                <h2 style='color: #2c5282;'>%TITULO%</h2>
                <p>Prezado(a) {$dados['nome']},</p>
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
    list($titulo, $conteudo) = match ($tipo) {
        'DAT' => [
            "DAT - Declaração de Acidente de Trânsito Concluída",
            "<p>Sua Declaração de Acidente de Trânsito (DAT) foi processada com sucesso!</p>
            <p><strong>Próximos Passos:</strong></p>
            <ol style='margin-left: 20px;'>
                <li>Compareça ao DEMUTRAN para retirar sua DAT</li>
                <li>Traga seu documento de identificação original</li>
                <li>Se possível, traga também o protocolo #{$id}</li>
            </ol>"
        ],
        'PCD' => [
            "Cartão de Estacionamento PCD - Solicitação Concluída",
            "<p>Sua solicitação do Cartão de Estacionamento PCD foi aprovada!</p>
            <p><strong>Próximos Passos:</strong></p>
            <ol style='margin-left: 20px;'>
                <li>Compareça ao DEMUTRAN para retirar seu cartão</li>
                <li>Traga os seguintes documentos:</li>
                <ul style='margin-left: 40px;'>
                    <li>Documento de identificação original</li>
                    <li>Laudo médico original</li>
                    <li>Comprovante de residência</li>
                </ul>
            </ol>"
        ],
        'JARI' => [
            "$tipoRecursoEspecifico - Análise Concluída",
            "<p>Seu processo de {$tipoRecursoEspecifico} foi analisado e processado!</p>
            <p><strong>Próximos Passos:</strong></p>
            <ol style='margin-left: 20px;'>
                <li>Compareça ao DEMUTRAN para receber o resultado</li>
                <li>Traga seu documento de identificação</li>
                <li>O prazo para recursos adicionais, se necessário, é de 30 dias</li>
            </ol>"
        ],
        'SAC' => [
            "Atendimento SAC - Solicitação Processada",
            "<p>Sua solicitação ao SAC foi processada!</p>
            <p><strong>Informações:</strong></p>
            <ul style='margin-left: 20px;'>
                <li>Protocolo: #{$id}</li>
                <li>Status: Concluído</li>
            </ul>
            <p>Caso necessite de informações adicionais, entre em contato com nossos canais de atendimento.</p>"
        ],
        'Parecer' => [
            "Parecer Técnico - Documento Disponível",
            "<p>Seu Parecer Técnico foi elaborado e está disponível!</p>
            <p><strong>Instruções:</strong></p>
            <ol style='margin-left: 20px;'>
                <li>O documento deve ser retirado pessoalmente no DEMUTRAN</li>
                <li>Apresente documento de identificação e o protocolo #{$id}</li>
                <li>O parecer tem validade de 90 dias após a emissão</li>
            </ol>"
        ],
        default => [
            "Protocolo Concluído",
            "<p>Seu protocolo #{$id} foi concluído com sucesso.</p>
            <p>Por favor, compareça ao DEMUTRAN para mais informações.</p>"
        ]
    };

    // Monta o email final
    $mensagemEmail = str_replace(
        ['%TITULO%', '%CONTEUDO%'],
        [$titulo, $conteudo],
        $baseTemplate
    );

    // Ao invés de enviar o email diretamente, retorna para preview
    if (isset($data['preview']) && $data['preview'] === true) {
        echo json_encode([
            'success' => true,
            'preview' => [
                'titulo' => $titulo,
                'conteudo' => $conteudo,
                'email' => $dados['email'],
                'nome' => $dados['nome']
            ]
        ]);
        exit;
    }

    // Se não for preview, verifica se temos confirmação
    if (!isset($data['confirmed']) || $data['confirmed'] !== true) {
        throw new Exception('É necessário confirmar o envio do email');
    }

    // Procede com o envio após confirmação
    error_log("Tentando enviar email confirmado...");
    $mensagemFinal = str_replace(
        ['%TITULO%', '%CONTEUDO%'],
        [$data['assunto'], $data['conteudo'] ?? $conteudo],
        $baseTemplate
    );

    $emailSuccess = sendMail(
        $data['email'] ?? $dados['email'],
        $dados['nome'],
        $data['assunto'] ?? $titulo,
        $mensagemFinal
    );

    if (!$emailSuccess) {
        throw new Exception('Falha no envio do email');
    }

    // Se o email foi enviado com sucesso, atualiza o status
    $sql = match ($tipo) {
        'SAC' => "UPDATE sac SET situacao = 'Concluído', is_read = 1 WHERE id = ?",
        'JARI' => "UPDATE solicitacoes_demutran SET situacao = 'Concluído', is_read = 1 WHERE id = ?",
        'PCD' => "UPDATE solicitacao_cartao SET situacao = 'Concluído', is_read = 1 WHERE id = ?",
        'DAT' => "UPDATE DAT1 SET situacao = 'Concluído', is_read = 1 WHERE id = ?",
        'Parecer' => "UPDATE Parecer SET situacao = 'Concluído', is_read = 1 WHERE id = ?",
        default => throw new Exception('Tipo de formulário inválido')
    };

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar status: ' . $stmt->error);
    }

    // Modificar esta parte do código onde registra o log
    $usuario_id = $_SESSION['usuario_id'];
    $data_hora = date('Y-m-d H:i:s');
    $acao = 'Concluiu';

    $sql_log = "INSERT INTO log_acoes (usuario_id, acao, tipo_formulario, formulario_id, data_hora) 
                VALUES (?, ?, ?, ?, ?)";
    $stmt_log = $conn->prepare($sql_log);
    $stmt_log->bind_param('issss', $usuario_id, $acao, $tipo, $id, $data_hora);
    $stmt_log->execute();

    // Retorna apenas uma resposta JSON
    echo json_encode([
        'success' => true,
        'message' => 'Formulário concluído e email enviado com sucesso'
    ]);

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();

    // Reverter a atualização do status em caso de erro
    if (isset($tabela) && isset($id)) {
        $sql_reverter = "UPDATE $tabela SET situacao = 'Pendente', is_read = 0 WHERE id = ?";
        $stmt_reverter = $conn->prepare($sql_reverter);
        $stmt_reverter->bind_param('i', $id);
        $stmt_reverter->execute();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar solicitação: ' . $e->getMessage()
    ]);
}
?>