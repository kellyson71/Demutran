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

    // Log para debug
    error_log("Tentando concluir formulário: Tipo=$tipo, ID=$id");

    // Se for DAT, precisamos buscar o token e usar a tabela correta
    if ($tipo === 'DAT') {
        // Primeiro, buscar o token na tabela central
        $sql_token = "SELECT token FROM formularios_dat_central WHERE id = ?";
        $stmt_token = $conn->prepare($sql_token);
        $stmt_token->bind_param('i', $id);
        $stmt_token->execute();
        $result = $stmt_token->get_result();
        $dat_data = $result->fetch_assoc();

        if (!$dat_data) {
            throw new Exception('Formulário DAT não encontrado');
        }

        $token = $dat_data['token'];
        error_log("Token DAT encontrado: $token");

        // Agora buscamos os dados na tabela DAT1 usando o token
        $sql_dados = "SELECT * FROM DAT1 WHERE token = ?";
        $stmt_dados = $conn->prepare($sql_dados);
        $stmt_dados->bind_param('s', $token);
    } else {
        // Determinar tabela correta baseado no tipobb
        $tabela = match ($tipo) {
            'SAC' => 'sac',
            'JARI' => 'solicitacoes_demutran',
            'PCD' => 'solicitacao_cartao',
            'Parecer' => 'parecer', // Corrigido para minúsculo
            default => throw new Exception('Tipo de formulário inválido')
        };

        // Buscar os dados antes de atualizar o status
        $sql_dados = "SELECT * FROM $tabela WHERE id = ?";
        $stmt_dados = $conn->prepare($sql_dados);
        $stmt_dados->bind_param('i', $id);
    }

    $stmt_dados->execute();
    $dados = $stmt_dados->get_result()->fetch_assoc();

    if (!$dados) {
        throw new Exception('Dados do formulário não encontrados');
    }

    // Preparar dados para o email
    $emailData = [
        'id' => $id,
        'tipo' => $tipo,
        'email' => $dados['email'] ?? '',
        'nome' => $dados['nome'] ?? '',
        'status' => 'Concluído'
    ];

    // Carregar biblioteca de email
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

    // Template de email
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
    if ($tipo === 'DAT') {
        // Atualiza DAT1 usando o token
        $sql = "UPDATE DAT1 SET situacao = 'Concluído', is_read = 1 WHERE token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $token);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar status em DAT1: ' . $stmt->error);
        }

        // Também atualiza formularios_dat_central
        $sql = "UPDATE formularios_dat_central SET situacao = 'Concluído', is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar status em formularios_dat_central: ' . $stmt->error);
        }

        error_log("Status atualizado nas tabelas DAT usando token: $token e ID: $id");
    } else {
        // Para os outros tipos de formulário
        $tabela = match ($tipo) {
            'SAC' => 'sac',
            'JARI' => 'solicitacoes_demutran',
            'PCD' => 'solicitacao_cartao',
            'Parecer' => 'parecer', // Corrigido para minúsculo
            default => throw new Exception('Tipo de formulário inválido')
        };

        $sql = "UPDATE $tabela SET situacao = 'Concluído', is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar status: ' . $stmt->error);
        }

        error_log("Status atualizado na tabela $tabela com ID: $id");
    }

    // Registra o log
    $usuario_id = $_SESSION['usuario_id'];
    $data_hora = date('Y-m-d H:i:s');
    $acao = 'Concluiu';

    $sql_log = "INSERT INTO log_acoes (usuario_id, acao, tipo_formulario, formulario_id, data_hora) 
                VALUES (?, ?, ?, ?, ?)";
    $stmt_log = $conn->prepare($sql_log);
    $stmt_log->bind_param('issss', $usuario_id, $acao, $tipo, $id, $data_hora);

    if (!$stmt_log->execute()) {
        error_log("Erro ao inserir no log: " . $stmt_log->error);
    }

    // Retorna apenas uma resposta JSON
    echo json_encode([
        'success' => true,
        'message' => 'Formulário concluído e email enviado com sucesso'
    ]);

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    error_log("Erro ao concluir formulário: " . $e->getMessage());

    // Reverter a atualização do status em caso de erro
    if (isset($tipo) && isset($id)) {
        try {
            if ($tipo === 'DAT' && isset($token)) {
                $sql_reverter = "UPDATE DAT1 SET situacao = 'Pendente', is_read = 0 WHERE token = ?";
                $stmt_reverter = $conn->prepare($sql_reverter);
                $stmt_reverter->bind_param('s', $token);
                $stmt_reverter->execute();

                $sql_reverter = "UPDATE formularios_dat_central SET situacao = 'Pendente', is_read = 0 WHERE id = ?";
                $stmt_reverter = $conn->prepare($sql_reverter);
                $stmt_reverter->bind_param('i', $id);
                $stmt_reverter->execute();
            } else if (isset($tabela)) {
                $sql_reverter = "UPDATE $tabela SET situacao = 'Pendente', is_read = 0 WHERE id = ?";
                $stmt_reverter = $conn->prepare($sql_reverter);
                $stmt_reverter->bind_param('i', $id);
                $stmt_reverter->execute();
            }
        } catch (Exception $reverterErro) {
            error_log("Erro ao reverter status: " . $reverterErro->getMessage());
        }
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar solicitação: ' . $e->getMessage()
    ]);
}
?>