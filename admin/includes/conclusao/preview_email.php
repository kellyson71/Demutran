<?php
session_start();
require_once '../../../env/config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

// Obter dados do POST (formato JSON)
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$tipo = $data['tipo'] ?? null;

if (!$id || !$tipo) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit();
}

try {
    // Obter dados do formulário baseado no tipo
    $formularioData = obterDadosFormulario($conn, $id, $tipo);

    if (!$formularioData) {
        throw new Exception('Formulário não encontrado');
    }

    // Gerar HTML do template de e-mail
    $emailHtml = gerarTemplateEmail($formularioData, $tipo);

    // Incluir o email de destino na resposta
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'html' => $emailHtml,
        'email_destino' => $formularioData['email'] ?? 'Email não encontrado'
    ]);
    exit();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}

// Função para obter os dados do formulário
function obterDadosFormulario($conn, $id, $tipo)
{
    $formularioData = [];

    switch ($tipo) {
        case 'JARI':
            // Obter dados do formulário JARI
            $sql = "SELECT * FROM solicitacoes_demutran WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $formularioData = $result->fetch_assoc();

            if ($formularioData) {
                // Adicionar informações específicas do JARI
                $formularioData['tipo_nome'] = obterNomeTipoSolicitacao($formularioData['tipo_solicitacao']);

                // Verificar e normalizar campo de status
                if (isset($formularioData['status'])) {
                    $formularioData['status_normalizado'] = $formularioData['status'];
                } elseif (isset($formularioData['situacao'])) {
                    $formularioData['status_normalizado'] = $formularioData['situacao'];
                } else {
                    $formularioData['status_normalizado'] = 'em_analise';
                }
            }
            break;

        case 'PCD':
            // Obter dados do formulário PCD
            $sql = "SELECT * FROM solicitacao_cartao WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $formularioData = $result->fetch_assoc();

            if ($formularioData) {
                // Adicionar informações específicas do PCD
                $formularioData['tipo_nome'] = $formularioData['tipo_solicitacao'] === 'idoso' ? 'Cartão do Idoso' : 'Cartão PCD';

                // Verificar e normalizar campo de status
                if (isset($formularioData['status'])) {
                    $formularioData['status_normalizado'] = $formularioData['status'];
                } elseif (isset($formularioData['situacao'])) {
                    $formularioData['status_normalizado'] = $formularioData['situacao'];
                } else {
                    $formularioData['status_normalizado'] = 'em_analise';
                }
            }
            break;

        case 'DAT':
            // Obter dados do formulário DAT diretamente
            $sql = "SELECT * FROM formularios_dat_central WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $formularioData = $result->fetch_assoc();

            if ($formularioData) {
                $formularioData['tipo_nome'] = 'Declaração de Acidente de Trânsito';

                // Verificar e normalizar campo de status
                if (isset($formularioData['status'])) {
                    $formularioData['status_normalizado'] = $formularioData['status'];
                } else {
                    $formularioData['status_normalizado'] = 'em_analise';
                }
            }
            break;

        case 'SAC':
            // Obter dados do formulário SAC
            $sql = "SELECT * FROM sac WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $formularioData = $result->fetch_assoc();

            if ($formularioData) {
                $formularioData['tipo_nome'] = 'Solicitação de Atendimento ao Cidadão';

                // Verificar e normalizar campo de status
                if (isset($formularioData['status'])) {
                    $formularioData['status_normalizado'] = $formularioData['status'];
                } elseif (isset($formularioData['situacao'])) {
                    $formularioData['status_normalizado'] = $formularioData['situacao'];
                } else {
                    $formularioData['status_normalizado'] = 'em_analise';
                }
            }
            break;

        case 'Parecer':
            // Obter dados do formulário Parecer
            $sql = "SELECT * FROM parecer WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $formularioData = $result->fetch_assoc();

            if ($formularioData) {
                $formularioData['tipo_nome'] = 'Parecer Técnico';

                // Verificar e normalizar campo de status
                if (isset($formularioData['status'])) {
                    $formularioData['status_normalizado'] = $formularioData['status'];
                } elseif (isset($formularioData['situacao'])) {
                    $formularioData['status_normalizado'] = $formularioData['situacao'];
                } else {
                    $formularioData['status_normalizado'] = 'em_analise';
                }
            }
            break;
    }

    return $formularioData;
}

// Função para obter nome amigável do tipo de solicitação
function obterNomeTipoSolicitacao($tipo)
{
    switch ($tipo) {
        case 'defesa_previa':
            return 'Defesa Prévia';
        case 'jari':
            return 'Recurso JARI';
        case 'apresentacao_condutor':
            return 'Apresentação de Condutor';
        default:
            return 'Solicitação';
    }
}

// Função para gerar o template de e-mail
function gerarTemplateEmail($dados, $tipo)
{
    // Obter o nome do destinatário
    $nome = $dados['nome'] ?? ($dados['nome_completo'] ?? 'Cidadão');

    // Obter o email do destinatário
    $emailDestinatario = $dados['email'] ?? 'Email não cadastrado';

    // Informações comuns do template
    $protocolo = $dados['id'];
    $tipoFormulario = $dados['tipo_nome'] ?? 'Formulário';
    $dataAtual = date('d/m/Y');

    // Adicionar informações de preview do email
    $previewInfo = '
    <div style="border: 1px solid #f59e0b; border-radius: 8px; padding: 15px; max-width: 800px; margin: 0 auto 20px auto; font-family: Arial, sans-serif; background-color: #fef3c7;">
        <h3 style="color: #d97706; margin-top: 0;">Informações do Email</h3>
        <div style="display: flex; margin-bottom: 8px;">
            <div style="width: 120px; font-weight: bold;">Para:</div>
            <div>' . htmlspecialchars($emailDestinatario) . '</div>
        </div>
        <div style="display: flex; margin-bottom: 8px;">
            <div style="width: 120px; font-weight: bold;">Assunto:</div>
            <div>Conclusão de ' . htmlspecialchars($tipoFormulario) . ' - Protocolo #' . htmlspecialchars($protocolo) . '</div>
        </div>
        <div style="font-size: 12px; margin-top: 10px; color: #92400e;">
            <p><strong>Nota:</strong> Este é apenas um preview do email que será enviado.</p>
        </div>
    </div>';

    // Template HTML básico
    $html = $previewInfo . '
    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://via.placeholder.com/150x80?text=LOGO" alt="Logo" style="max-width: 150px;">
            <h2 style="color: #2563eb; margin-top: 10px;">Departamento de Trânsito</h2>
        </div>
        
        <div style="background-color: #f1f5f9; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
            <h3 style="color: #334155; margin-top: 0;">Conclusão de ' . htmlspecialchars($tipoFormulario) . '</h3>
            <p style="color: #64748b;">Protocolo: <strong>#' . htmlspecialchars($protocolo) . '</strong></p>
            <p style="color: #64748b;">Data: <strong>' . htmlspecialchars($dataAtual) . '</strong></p>
        </div>
        
        <div style="margin-bottom: 20px;">
            <p>Prezado(a) <strong>' . htmlspecialchars($nome) . '</strong>,</p>
            <p>Informamos que sua solicitação de <strong>' . htmlspecialchars($tipoFormulario) . '</strong> foi processada e concluída.</p>
            <p>Segue abaixo um resumo da sua solicitação:</p>
        </div>';

    // Adiciona informações específicas baseadas no tipo de formulário
    $html .= gerarConteudoEspecifico($dados, $tipo);

    $html .= '
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <p>Caso tenha alguma dúvida, por favor entre em contato conosco.</p>
            <p>Atenciosamente,<br>Equipe do Departamento de Trânsito</p>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px;">
            <p>Este é um e-mail automático. Por favor, não responda a esta mensagem.</p>
            <p>&copy; ' . date('Y') . ' Departamento de Trânsito. Todos os direitos reservados.</p>
        </div>
    </div>';

    return $html;
}

// Função para gerar conteúdo específico baseado no tipo de formulário
function gerarConteudoEspecifico($dados, $tipo)
{
    $html = '<div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin-bottom: 20px;">';

    switch ($tipo) {
        case 'JARI':
            $html .= '
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Tipo de Solicitação:</p>
                    <p>' . htmlspecialchars($dados['tipo_nome']) . '</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Auto de Infração:</p>
                    <p>' . htmlspecialchars($dados['auto_infracao'] ?? 'Não informado') . '</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Placa do Veículo:</p>
                    <p>' . htmlspecialchars($dados['placa'] ?? 'Não informado') . '</p>
                </div>
                <div>
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Resultado:</p>
                    <p>Sua solicitação foi analisada e ' . (isset($dados['status_normalizado']) && $dados['status_normalizado'] === 'aprovado' ? 'aprovada' : 'processada') . '.</p>
                </div>';
            break;

        case 'PCD':
            $html .= '
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Tipo de Cartão:</p>
                    <p>' . htmlspecialchars($dados['tipo_nome']) . '</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">CPF:</p>
                    <p>' . htmlspecialchars($dados['cpf'] ?? 'Não informado') . '</p>
                </div>
                <div>
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Resultado:</p>
                    <p>Sua solicitação de cartão foi analisada e ' . (isset($dados['status_normalizado']) && $dados['status_normalizado'] === 'aprovado' ? 'aprovada' : 'processada') . '.</p>
                </div>';
            break;

        case 'DAT':
            $html .= '
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Data do Acidente:</p>
                    <p>' . htmlspecialchars($dados['data_acidente'] ?? 'Não informado') . '</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Local do Acidente:</p>
                    <p>' . htmlspecialchars($dados['local_acidente'] ?? 'Não informado') . '</p>
                </div>
                <div>
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Resultado:</p>
                    <p>Sua declaração de acidente foi processada e está disponível para consulta.</p>
                </div>';
            break;

        case 'SAC':
            $html .= '
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Assunto:</p>
                    <p>' . htmlspecialchars($dados['assunto'] ?? 'Não informado') . '</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Número do Protocolo:</p>
                    <p>#' . htmlspecialchars($dados['id']) . '</p>
                </div>
                <div>
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Resposta:</p>
                    <p>' . htmlspecialchars($dados['resposta'] ?? 'Sua solicitação foi analisada e processada pela nossa equipe.') . '</p>
                </div>';
            break;

        case 'Parecer':
            $html .= '
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Número do Parecer:</p>
                    <p>' . htmlspecialchars($dados['numero_parecer'] ?? $dados['id']) . '</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Assunto:</p>
                    <p>' . htmlspecialchars($dados['assunto'] ?? 'Não informado') . '</p>
                </div>
                <div>
                    <p style="font-weight: bold; margin-bottom: 5px; color: #334155;">Resultado:</p>
                    <p>Seu parecer técnico foi analisado e ' . (isset($dados['status_normalizado']) && $dados['status_normalizado'] === 'aprovado' ? 'aprovado' : 'processado') . '.</p>
                </div>';
            break;

        default:
            $html .= '<p>Detalhes da solicitação não disponíveis.</p>';
    }

    $html .= '</div>';
    return $html;
}
