<?php
session_start();
header('Content-Type: application/json');
require_once '../../env/config.php';
require_once '../../utils/mail.php';

// Incluir templates de email
require_once '../templates/emails/base_template.php';
require_once '../templates/emails/dat.php';
require_once '../templates/emails/jari.php';
require_once '../templates/emails/pcd.php';
require_once '../templates/emails/sac.php';
require_once '../templates/emails/parecer.php';

// Função para tratar erros
function responder($sucesso, $mensagem, $dados = [])
{
    echo json_encode([
        'success' => $sucesso,
        'message' => $mensagem,
        'data' => $dados
    ]);
    exit;
}

try {
    // Verificar autenticação
    if (!isset($_SESSION['usuario_id'])) {
        responder(false, 'Usuário não autenticado');
    }

    // Obter dados da requisição
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id']) || !isset($data['tipo'])) {
        responder(false, 'Dados incompletos');
    }

    $id = $data['id'];
    $tipo = $data['tipo'];
    $isPreview = isset($data['preview']) && $data['preview'] === true;
    $isConfirmed = isset($data['confirmed']) && $data['confirmed'] === true;

    // Buscar dados do formulário conforme o tipo
    $dados = [];
    $token = null;

    if ($tipo === 'DAT') {
        // Buscar token na tabela central
        $sql_token = "SELECT token FROM formularios_dat_central WHERE id = ?";
        $stmt_token = $conn->prepare($sql_token);
        $stmt_token->bind_param('i', $id);
        $stmt_token->execute();
        $result = $stmt_token->get_result();
        $dat_data = $result->fetch_assoc();

        if (!$dat_data) {
            responder(false, 'Formulário DAT não encontrado');
        }

        $token = $dat_data['token'];

        // Buscar dados na tabela DAT1
        $sql_dados = "SELECT * FROM DAT1 WHERE token = ?";
        $stmt_dados = $conn->prepare($sql_dados);
        $stmt_dados->bind_param('s', $token);
        $stmt_dados->execute();
        $dados = $stmt_dados->get_result()->fetch_assoc();
    } else {
        // Determinar tabela correta baseado no tipo
        $tabela = match ($tipo) {
            'SAC' => 'sac',
            'JARI' => 'solicitacoes_demutran',
            'PCD' => 'solicitacao_cartao',
            'Parecer' => 'parecer',
            default => null
        };

        if (!$tabela) {
            responder(false, 'Tipo de formulário inválido');
        }

        // Buscar dados
        $sql_dados = "SELECT * FROM $tabela WHERE id = ?";
        $stmt_dados = $conn->prepare($sql_dados);
        $stmt_dados->bind_param('i', $id);
        $stmt_dados->execute();
        $dados = $stmt_dados->get_result()->fetch_assoc();
    }

    if (!$dados) {
        responder(false, 'Dados do formulário não encontrados');
    }

    // Preparar conteúdo do email com base no tipo de formulário
    $emailContent = [];

    switch ($tipo) {
        case 'DAT':
            $emailContent = getDATemailContent($id);
            break;
        case 'JARI':
            $subTipo = $dados['tipo_solicitacao'] ?? '';
            $emailContent = getJARIemailContent($subTipo);
            break;
        case 'PCD':
            $subTipo = $dados['tipo_solicitacao'] ?? 'pcd';
            $emailContent = getPCDemailContent($subTipo);
            break;
        case 'SAC':
            $emailContent = getSACemailContent($id);
            break;
        case 'Parecer':
            $emailContent = getParecerEmailContent($id);
            break;
        default:
            $emailContent = [
                'titulo' => 'Protocolo Concluído',
                'conteudo' => "<p>Seu protocolo #{$id} foi concluído com sucesso.</p><p>Por favor, compareça ao DEMUTRAN para mais informações.</p>"
            ];
    }

    // Se for preview, retorna os dados para o modal
    if ($isPreview) {
        responder(true, 'Preview gerado com sucesso', [
            'preview' => [
                'titulo' => $emailContent['titulo'],
                'conteudo' => $emailContent['conteudo'],
                'email' => $dados['email'] ?? '',
                'nome' => $dados['nome'] ?? ''
            ]
        ]);
    }

    // Se não for confirmado, não prossegue
    if (!$isConfirmed) {
        responder(false, 'É necessário confirmar o envio do email');
    }

    // Usar dados customizados de email, se fornecidos
    $emailAssunto = $data['assunto'] ?? $emailContent['titulo'];
    $emailTexto = $data['conteudo'] ?? $emailContent['conteudo'];
    $emailDestino = $data['email'] ?? $dados['email'] ?? '';
    $nome = $dados['nome'] ?? '';

    // Preparar email completo usando o template base
    $mensagemCompleta = getEmailTemplate($emailAssunto, $nome, $emailTexto);

    // Enviar email
    $emailSuccess = sendMail(
        $emailDestino,
        $nome,
        $emailAssunto,
        $mensagemCompleta
    );

    if (!$emailSuccess) {
        responder(false, 'Falha no envio do email');
    }

    // Atualizar status do formulário
    if ($tipo === 'DAT') {
        // Atualiza DAT1 usando o token
        $sql = "UPDATE DAT1 SET situacao = 'Concluído', is_read = 1 WHERE token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $token);
        $stmt->execute();

        // Também atualiza formularios_dat_central
        $sql = "UPDATE formularios_dat_central SET situacao = 'Concluído', is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    } else {
        // Para os outros tipos de formulário
        $sql = "UPDATE $tabela SET situacao = 'Concluído', is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    // Registra o log
    $usuario_id = $_SESSION['usuario_id'];
    $data_hora = date('Y-m-d H:i:s');
    $acao = 'Concluiu';

    $sql_log = "INSERT INTO log_acoes (usuario_id, acao, tipo_formulario, formulario_id, data_hora) 
                VALUES (?, ?, ?, ?, ?)";
    $stmt_log = $conn->prepare($sql_log);
    $stmt_log->bind_param('issss', $usuario_id, $acao, $tipo, $id, $data_hora);
    $stmt_log->execute();

    // Retorna sucesso
    responder(true, 'Formulário concluído e email enviado com sucesso');
} catch (Throwable $e) {
    // Log do erro
    error_log("Erro ao concluir formulário: " . $e->getMessage());
    responder(false, 'Erro ao processar solicitação: ' . $e->getMessage());
}
