<?php
session_start();
include '../env/config.php';
include './includes/template.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obter o ID e o tipo do formulário via GET
$id = $_GET['id'] ?? null;
$tipo = $_GET['tipo'] ?? null;

if (!$id || !$tipo) {
    header('Location: formularios.php');
    exit();
}

// Função para buscar os dados do formulário
function buscarDadosFormulario($conn, $id, $tipo) {
    $tabela = match($tipo) {
        'SAC' => 'sac',
        'JARI' => 'solicitacoes_demutran',
        'PCD' => 'solicitacao_cartao',
        'DAT' => 'DAT1',
        'Parecer' => 'Parecer',
        default => null
    };

    if (!$tabela) {
        return null;
    }

    $sql = "SELECT * FROM $tabela WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Buscar dados do formulário
$dados = buscarDadosFormulario($conn, $id, $tipo);

if (!$dados) {
    header('Location: formularios.php');
    exit();
}

// Função para formatar o nome do campo
function formatarCampo($campo) {
    $campo = str_replace('_', ' ', $campo);
    return ucfirst($campo);
}

// Campos que devem ser tratados como links
$camposLink = ['arquivo_anexo', 'documento', 'comprovante', 'midia'];

// Campos que devem ser ocultados
$camposOcultos = ['id', 'token', 'usuario_id'];

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

// Configuração dos campos
$configCampos = [
    'nome' => [
        'tipo' => 'texto',
        'label' => 'Nome Completo',
        'icone' => 'bx-user',
        'classe' => 'campo-destaque',
        'editavel' => true
    ],
    'cpf' => [
        'tipo' => 'documento',
        'label' => 'CPF',
        'icone' => 'bx-id-card',
        'mascara' => '000.000.000-00',
        'editavel' => true
    ],
    'email' => [
        'tipo' => 'email',
        'label' => 'E-mail',
        'icone' => 'bx-envelope',
        'editavel' => true
    ],
    'telefone' => [
        'tipo' => 'telefone',
        'label' => 'Telefone',
        'icone' => 'bx-phone',
        'mascara' => '(00) 00000-0000',
        'editavel' => true
    ],
    'doc_identidade_url' => [
        'tipo' => 'arquivo',
        'label' => 'Documento de Identidade',
        'icone' => 'bx-file',
        'editavel' => false,
        'preview' => true
    ],
    'comprovante_residencia_url' => [
        'tipo' => 'arquivo',
        'label' => 'Comprovante de Residência',
        'icone' => 'bx-home',
        'editavel' => false,
        'preview' => true
    ],
    // Adicionar mais configurações conforme necessário
];

// Adicionar após as configurações existentes
$tiposFormulario = [
    'SAC' => [
        'solicitacao' => [
            'cor' => 'blue',
            'icone' => 'bx-file',
            'label' => 'Solicitação'
        ],
        'reclamacao' => [
            'cor' => 'red',
            'icone' => 'bx-message-alt-error',
            'label' => 'Reclamação'
        ],
        'sugestao' => [
            'cor' => 'green',
            'icone' => 'bx-bulb',
            'label' => 'Sugestão'
        ]
    ],
    'JARI' => [
        'defesa_previa' => [
            'cor' => 'purple',
            'icone' => 'bx-shield',
            'label' => 'Defesa Prévia'
        ],
        'recurso_jari' => [
            'cor' => 'orange',
            'icone' => 'bx-file',
            'label' => 'Recurso JARI'
        ],
        'apresentacao_condutor' => [
            'cor' => 'cyan',
            'icone' => 'bx-user',
            'label' => 'Apresentação do Condutor'
        ]
    ],
    'PCD' => [
        'idoso' => [
            'cor' => 'teal',
            'icone' => 'bx-user',
            'label' => 'Cartão Idoso'
        ],
        'pcd' => [
            'cor' => 'indigo',
            'icone' => 'bx-wheelchair',
            'label' => 'Cartão PCD'
        ]
    ]
];

// Função para renderizar campo baseado no tipo
function renderizarCampo($campo, $valor, $config) {
    global $tiposFormulario, $tipo; // Adicionar acesso à variável global
    
    $fieldId = htmlspecialchars($campo);
    $valorSeguro = $valor ?? '';
    $tipoCampo = $config['tipo'] ?? 'texto';
    $editavel = $config['editavel'] ?? true;
    
    ob_start();
    ?>
    <div id="container_<?php echo $fieldId; ?>" 
         class="campo-container <?php echo $config['classe'] ?? ''; ?> border-b border-gray-200 pb-4 group hover:bg-gray-50 rounded-lg p-3 transition-colors">
        <div class="flex justify-between items-center">
            <label class="flex items-center gap-2 text-sm font-medium text-gray-600 mb-1">
                <?php if (isset($config['icone'])): ?>
                    <i class='bx <?php echo $config['icone']; ?>'></i>
                <?php endif; ?>
                <?php echo $config['label'] ?? formatarCampo($campo); ?>
            </label>
            
            <?php if ($editavel): ?>
                <button onclick="toggleEdit('<?php echo $fieldId; ?>')" 
                        id="editIcon_<?php echo $fieldId; ?>"
                        class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-blue-100 rounded">
                    <i class='bx bx-pencil text-blue-600'></i>
                </button>
            <?php endif; ?>
        </div>

        <?php if ($tipoCampo === 'arquivo' && !empty($valorSeguro)): ?>
            <div class="arquivo-preview">
                <?php if ($config['preview'] ?? false): ?>
                    <div class="preview-container">
                        <!-- Adicionar preview específico por tipo de arquivo -->
                    </div>
                <?php endif; ?>
                <a href="<?php echo htmlspecialchars($valorSeguro); ?>" 
                   target="_blank"
                   class="text-blue-500 hover:text-blue-700 flex items-center gap-2">
                    <i class='bx bx-link-external'></i>
                    Visualizar anexo
                </a>
            </div>
        <?php else: ?>
            <div id="display_<?php echo $fieldId; ?>" 
                 data-value="<?php echo htmlspecialchars($valorSeguro); ?>"
                 class="campo-valor campo-tipo-<?php echo $tipoCampo; ?> text-gray-900">
                <?php echo !empty($valorSeguro) ? htmlspecialchars($valorSeguro) : 
                    '<span class="text-gray-400">Não informado</span>'; ?>
            </div>
            <?php if ($editavel): ?>
                <input type="<?php echo $tipoCampo === 'email' ? 'email' : 'text'; ?>" 
                       id="editor_<?php echo $fieldId; ?>"
                       class="hidden w-full p-2 border rounded-md"
                       value="<?php echo htmlspecialchars($valorSeguro); ?>"
                       <?php if (isset($config['mascara'])): ?>
                       data-mascara="<?php echo $config['mascara']; ?>"
                       <?php endif; ?>
                       onkeydown="handleKeyPress(event, '<?php echo $fieldId; ?>')"
                       onblur="cancelarEdicao('<?php echo $fieldId; ?>')">
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
    // Modificar a lógica de verificação dos tipos
    if ($campo === 'tipo_contato' || $campo === 'tipo_solicitacao') {
        $tipoValor = strtolower(str_replace(' ', '-', $valorSeguro));
        
        // Verificar se o tipo existe na configuração
        if (isset($tiposFormulario[$tipo][$tipoValor])) {
            $configTipo = $tiposFormulario[$tipo][$tipoValor];
            echo "<div class='tipo-badge tipo-{$tipoValor}'>";
            echo "<i class='bx {$configTipo['icone']}'></i>";
            echo $configTipo['label'];
            echo "</div>";
        } else {
            // Fallback para tipos não configurados
            echo "<div class='tipo-badge tipo-default'>";
            echo "<i class='bx bx-info-circle'></i>";
            echo ucfirst($tipoValor);
            echo "</div>";
        }
    }
    
    if ($campo === 'n_cartao') {
        echo "<div class='numero-cartao'>";
        echo "<i class='bx bx-id-card mr-2'></i>";
        echo $valorSeguro;
        echo "</div>";
    }
    
    if ($campo === 'data_submissao') {
        echo "<div class='data-submissao'>";
        echo "<i class='bx bx-time-five'></i>";
        echo "<span class='data-relativa' data-date='{$valorSeguro}'></span>";
        echo "</div>";
    }
    
    return ob_get_clean();
}

// Adicionar função para enviar notificação por email
function enviarEmailConclusao($tipo, $dados) {
    $email = '';
    $nome = $dados['nome'] ?? 'Usuário';
    
    // Determinar o email correto baseado no tipo de formulário
    switch($tipo) {
        case 'SAC':
        case 'Parecer':
        case 'DAT':
            $email = $dados['email'];
            break;
        case 'JARI':
            // Priorizar gmail se existir
            $email = $dados['gmail'] ?? $dados['email'] ?? '';
            break;
        case 'PCD':
            // Se tiver representante, enviar para ambos
            $emails = array_filter([
                $dados['email'],
                $dados['email_representante'] ?? ''
            ]);
            $email = implode(',', $emails);
            break;
    }

    if (empty($email)) return false;

    // Preparar mensagem baseada no tipo
    $tipoFormatado = match($tipo) {
        'SAC' => 'SAC',
        'JARI' => 'Recurso/Defesa',
        'PCD' => 'Cartão ' . ($dados['tipo_solicitacao'] ?? 'Especial'),
        'DAT' => 'Declaração de Acidente de Trânsito',
        'Parecer' => 'Parecer Técnico',
        default => 'Solicitação'
    };

    // Construir mensagem HTML
    $mensagem = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #1e40af;'>Atualização de Status - {$tipoFormatado}</h2>
        <p>Olá, {$nome}!</p>
        <p>Seu protocolo #{$dados['id']} foi concluído com sucesso.</p>
        <hr style='border: 1px solid #e5e7eb; margin: 20px 0;'>
        <p style='color: #4b5563; font-size: 14px;'>
            Para mais informações, acesse o portal do DEMUTRAN ou entre em contato conosco.
        </p>
    </div>";

    // Preparar dados para o envio
    $postData = [
        'email' => $email,
        'nome' => $nome,
        'assunto' => "Protocolo #{$dados['id']} - Concluído",
        'mensagem' => $mensagem
    ];

    // Fazer requisição para mail.php
    $ch = curl_init('../utils/mail.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Formulário</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
    function toggleEdit(fieldId) {
        const container = document.getElementById(`container_${fieldId}`);
        const display = document.getElementById(`display_${fieldId}`);
        const editor = document.getElementById(`editor_${fieldId}`);
        const editIcon = document.getElementById(`editIcon_${fieldId}`);

        if (display.classList.contains('hidden')) {
            // Salvando
            const novoValor = editor.value;
            if (novoValor !== display.dataset.value) {
                salvarEdicao(fieldId, novoValor);
            } else {
                cancelarEdicao(fieldId);
            }
        } else {
            // Entrando no modo de edição
            display.classList.add('hidden');
            editor.classList.remove('hidden');
            editor.focus();
            editIcon.innerHTML = '<i class="bx bx-check text-green-600"></i>';
            container.classList.add('editing');
        }
    }

    function cancelarEdicao(fieldId) {
        const display = document.getElementById(`display_${fieldId}`);
        const editor = document.getElementById(`editor_${fieldId}`);
        const editIcon = document.getElementById(`editIcon_${fieldId}`);
        const container = document.getElementById(`container_${fieldId}`);

        display.classList.remove('hidden');
        editor.classList.add('hidden');
        editIcon.innerHTML = '<i class="bx bx-pencil text-blue-600"></i>';
        container.classList.remove('editing');
    }

    function salvarEdicao(campo, novoValor) {
        const loadingIcon = `<svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>`;

        const editIcon = document.getElementById(`editIcon_${campo}`);
        editIcon.innerHTML = loadingIcon;

        fetch('editar_formulario_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: <?php echo $id; ?>,
                tipo: '<?php echo $tipo; ?>',
                campo: campo,
                novoValor: novoValor
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const display = document.getElementById(`display_${campo}`);
                display.textContent = novoValor;
                display.dataset.value = novoValor;
                cancelarEdicao(campo);
                
                // Mostrar notificação de sucesso
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-lg';
                notification.innerHTML = '<i class="bx bx-check mr-2"></i> Campo atualizado com sucesso!';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 3000);
            } else {
                throw new Error(data.message || 'Erro ao atualizar');
            }
        })
        .catch(error => {
            alert('Erro ao salvar: ' + error.message);
            cancelarEdicao(campo);
        });
    }

    function handleKeyPress(event, fieldId) {
        if (event.key === 'Enter') {
            toggleEdit(fieldId);
        } else if (event.key === 'Escape') {
            cancelarEdicao(fieldId);
        }
    }

    // Adicionar função para aplicar máscaras
    function aplicarMascaras() {
        document.querySelectorAll('input[data-mascara]').forEach(input => {
            VMasker(input).maskPattern(input.dataset.mascara);
        });
    }

    function concluirFormulario() {
        const loadingIcon = `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>`;

        const btnConcluir = document.getElementById('btnConcluir');
        const btnText = btnConcluir.innerHTML;
        btnConcluir.innerHTML = loadingIcon;
        btnConcluir.disabled = true;

        fetch('concluir_formulario_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: <?php echo $id; ?>,
                tipo: '<?php echo $tipo; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar UI
                const statusBadge = document.getElementById('statusBadge');
                statusBadge.className = 'px-3 py-1 rounded-full text-sm bg-green-100 text-green-800';
                statusBadge.textContent = 'Concluído';
                
                // Mostrar notificação
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-lg';
                notification.innerHTML = '<i class="bx bx-check mr-2"></i> Formulário concluído com sucesso!';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 3000);

                // Remover botão de conclusão
                btnConcluir.remove();
            } else {
                throw new Error(data.message || 'Erro ao concluir');
            }
        })
        .catch(error => {
            alert('Erro ao concluir: ' + error.message);
            btnConcluir.innerHTML = btnText;
            btnConcluir.disabled = false;
        });
    }
    </script>
    <style>
    /* Estilos base */
    .campo-destaque {
        background-color: rgba(59, 130, 246, 0.05);
    }
    .campo-tipo-documento {
        font-family: monospace;
    }
    .arquivo-preview {
        max-width: 100%;
        overflow: hidden;
    }
    .preview-container {
        max-height: 200px;
        overflow: hidden;
        margin-bottom: 10px;
        border-radius: 4px;
    }

    /* Badge de tipo */
    .tipo-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        gap: 0.5rem;
    }
    .tipo-badge i {
        font-size: 1.25rem;
    }

    /* Cores para tipos específicos */
    .tipo-solicitacao {
        background-color: #dbeafe;
        color: #1e40af;
    }
    .tipo-reclamacao {
        background-color: #fee2e2;
        color: #991b1b;
    }
    .tipo-sugestao {
        background-color: #dcfce7;
        color: #166534;
    }
    .tipo-defesa-previa {
        background-color: #f3e8ff;
        color: #6b21a8;
    }
    .tipo-recurso-jari {
        background-color: #ffedd5;
        color: #9a3412;
    }
    .tipo-apresentacao-condutor {
        background-color: #cffafe;
        color: #155e75;
    }
    .tipo-idoso {
        background-color: #ccfbf1;
        color: #134e4a;
    }
    .tipo-pcd {
        background-color: #e0e7ff;
        color: #3730a3;
    }
    .tipo-default {
        background-color: #f3f4f6;
        color: #374151;
    }

    /* Número do cartão */
    .numero-cartao {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background-color: #f3f4f6;
        border-radius: 0.5rem;
        font-family: monospace;
        font-size: 1.125rem;
        font-weight: 700;
        color: #374151;
        border: 2px solid #e5e7eb;
    }

    /* Data de submissão */
    .data-submissao {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.5rem;
    }
    .data-submissao i {
        color: #9ca3af;
    }

    /* Container principal */
    .info-principal {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding: 1rem;
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
</style>

    <script>
    // Função para formatar data relativa
    function formatarDataRelativa(data) {
        const agora = new Date();
        const dataSubmissao = new Date(data);
        const diff = Math.floor((agora - dataSubmissao) / 1000);

        if (diff < 60) return 'Agora mesmo';
        if (diff < 3600) return `${Math.floor(diff / 60)} minutos atrás`;
        if (diff < 86400) return `${Math.floor(diff / 3600)} horas atrás`;
        if (diff < 2592000) return `${Math.floor(diff / 86400)} dias atrás`;
        
        return dataSubmissao.toLocaleDateString('pt-BR');
    }

    // Atualizar todas as datas ao carregar
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.data-relativa').forEach(el => {
            const data = el.getAttribute('data-date');
            el.textContent = formatarDataRelativa(data);
        });
    });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-masker/1.2.0/vanilla-masker.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 flex flex-col h-full">
                <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                <?php echo getSidebarHtml('formularios'); ?>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php 
            $topbarHtml = getTopbarHtml('Visualizar Formulário', $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6">
                <div class="container mx-auto px-4 py-8">
                    <!-- Cabeçalho -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">
                                    Protocolo #<?php echo $id; ?>
                                </h1>
                                <p class="text-gray-600">
                                    Tipo: <?php echo $tipo; ?>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <a href="formularios.php" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">
                                    <i class='bx bx-arrow-back'></i> Voltar
                                </a>
                                <button class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                    <i class='bx bx-edit-alt'></i> Editar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Conteúdo -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Informações Principais -->
                            <div class="info-principal md:col-span-2">
                                <?php
                                $camposPrincipais = ['tipo_contato', 'tipo_solicitacao', 'n_cartao'];
                                foreach ($camposPrincipais as $campoPrincipal) {
                                    if (isset($dados[$campoPrincipal])) {
                                        echo renderizarCampo($campoPrincipal, $dados[$campoPrincipal], $configCampos[$campoPrincipal] ?? []);
                                    }
                                }
                                ?>
                                <div class="data-submissao">
                                    <i class='bx bx-calendar'></i>
                                    <span class="data-relativa" data-date="<?php echo $dados['data_submissao']; ?>"></span>
                                </div>
                            </div>

                            <!-- Demais campos -->
                            <?php foreach ($dados as $campo => $valor): 
                                if (in_array($campo, $camposOcultos) || in_array($campo, $camposPrincipais)) continue;
                                
                                $config = $configCampos[$campo] ?? [
                                    'tipo' => 'texto',
                                    'editavel' => true
                                ];
                                
                                echo renderizarCampo($campo, $valor, $config);
                            endforeach; ?>
                        </div>
                    </div>

                    <!-- Status do Formulário -->
                    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Status do Formulário</h2>
                        <div class="flex items-center gap-4">
                            <span id="statusBadge" class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-800">
                                Em processamento
                            </span>
                            <?php if ($dados['situacao'] !== 'Concluído'): ?>
                                <button id="btnConcluir" 
                                        onclick="concluirFormulario()" 
                                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                    <i class='bx bx-check'></i> Marcar como concluído
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">&copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os direitos reservados.</p>
            </footer>
        </div>
    </div>
</body>
</html>