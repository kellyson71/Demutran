<?php
session_start();
require_once '../env/config.php';
require_once './includes/template.php';

// Verifica se o usuário está logado
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

// Atualizar o status de leitura baseado no tipo do formulário
$tableName = '';
switch ($tipo) {
    case 'JARI':
        $tableName = 'solicitacoes_demutran';
        break;
    case 'PCD':
        $tableName = 'solicitacao_cartao';
        break;
    case 'DAT':
        // Para DAT, precisamos buscar o ID correto na tabela formularios_dat_central usando o token
        $sql = "SELECT fc.id, fc.token FROM DAT4 d4 
                INNER JOIN formularios_dat_central fc ON d4.token = fc.token 
                WHERE d4.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $tableName = 'formularios_dat_central';
            $id = $row['id']; // Atualiza o ID para o da tabela correta
        }
        break;
    case 'SAC':
        $tableName = 'sac';
        break;
    case 'Parecer':
        $tableName = 'parecer';
        break;
}

if ($tableName) {
    $sql = "UPDATE $tableName SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

// Função para obter o título do formulário baseado no tipo e subtipo
function obterTituloFormulario($tipo, $conn, $id)
{
    switch ($tipo) {
        case 'JARI':
            $sql = "SELECT tipo_solicitacao FROM solicitacoes_demutran WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $dados = $result->fetch_assoc();

            switch ($dados['tipo_solicitacao'] ?? '') {
                case 'defesa_previa':
                    return 'Defesa Prévia';
                case 'jari':
                    return 'Recurso JARI';
                case 'apresentacao_condutor':
                    return 'Apresentação de Condutor';
                default:
                    return 'Formulário de Defesa';
            }

        case 'PCD':
            $sql = "SELECT tipo_solicitacao FROM solicitacao_cartao WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $dados = $result->fetch_assoc();

            return ($dados['tipo_solicitacao'] ?? '') === 'idoso' ? 'Cartão Idoso' : 'Cartão PCD';

        case 'DAT':
            return 'Declaração de Acidente de Trânsito';

        case 'SAC':
            return 'Atendimento ao Cidadão';

        case 'Parecer':
            return 'Parecer Técnico';

        default:
            return 'Detalhes do Formulário';
    }
}

$tituloFormulario = obterTituloFormulario($tipo, $conn, $id);
$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }">

<head>
    <meta charset="UTF-8">
    <title><?php echo $tituloFormulario; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Google Fonts (Roboto) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        .title-animation {
            animation: slideDown 0.6s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .title-header {
            background-color: #2563eb;
        }

        .editable-field {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .edit-button {
            opacity: 0;
            transition: opacity 0.2s;
        }

        .editable-field:hover .edit-button {
            opacity: 1;
        }

        .field-input {
            background-color: transparent;
            border: 1px solid transparent;
            padding: 0.25rem;
            transition: all 0.3s;
        }

        .field-input.editing {
            background-color: white;
            border-color: #e2e8f0;
            border-radius: 0.375rem;
        }
    </style>
</head>

<body class="bg-gray-100 font-roboto min-h-screen flex flex-col">
    <!-- Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 flex flex-col h-full">
                <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                <?php echo getSidebarHtml('formularios'); ?>
            </div>
        </aside>

        <!-- Mobile Sidebar -->
        <div x-show="open" @click.away="open = false" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden">
            <aside class="w-64 bg-white h-full shadow-md">
                <div class="p-6">
                    <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                    <?php echo getSidebarHtml('formularios'); ?>
                </div>
            </aside>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php
            $topbarHtml = getTopbarHtml($tituloFormulario, $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Botão Voltar -->
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <?php
                        // Recupera os parâmetros da URL anterior
                        $pagina = $_GET['pagina_anterior'] ?? '';
                        $search = $_GET['search_anterior'] ?? '';
                        $tipo_filtro = $_GET['tipo_anterior'] ?? '';
                        $view = $_GET['view_anterior'] ?? '';

                        // Constrói a URL de retorno com os parâmetros
                        $params = [];
                        if ($pagina) $params[] = "pagina=" . urlencode($pagina);
                        if ($search) $params[] = "search=" . urlencode($search);
                        if ($tipo_filtro) $params[] = "tipo=" . urlencode($tipo_filtro);
                        if ($view) $params[] = "view=" . urlencode($view);

                        $url_retorno = "formularios.php" . (!empty($params) ? "?" . implode("&", $params) : "");
                        ?>
                        <a href="<?php echo $url_retorno; ?>"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition-colors duration-200 shadow-sm">
                            <span class="material-icons mr-2">arrow_back</span>
                            Voltar
                        </a>
                    </div>
                    <div>
                        <?php
                        // Determina a URL do formulário baseado no tipo
                        $form_url = '';
                        switch ($tipo) {
                            case 'JARI':
                                // Buscar o subtipo (tipo_solicitacao) para JARI
                                $sql = "SELECT tipo_solicitacao FROM solicitacoes_demutran WHERE id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param('i', $id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $dados_jari = $result->fetch_assoc();

                                if ($dados_jari['tipo_solicitacao'] === 'apresentacao_condutor') {
                                    $form_url = "../utils/form/gerar_formulario_AP.php";
                                } else {
                                    $form_url = "../utils/form/gerar_formulario.php";
                                }
                                break;
                            case 'PCD':
                                $form_url = "../utils/form/gerar_formulario_cartao.php";
                                break;
                            case 'DAT':
                                $form_url = "../utils/form/gerar_formulario_DAT.php";
                                break;
                            case 'Parecer':
                                $form_url = "../utils/form/gerar_formulario_parecer.php";
                                break;
                            default:
                                $form_url = "../utils/form/gerar_formulario.php";
                        }

                        // Adiciona parâmetros necessários
                        $form_url .= "?id=" . urlencode($id) . "&tipo=" . urlencode(strtolower($tipo));
                        if (isset($dados_jari['tipo_solicitacao'])) {
                            $form_url .= "&tipo_solicitacao=" . urlencode($dados_jari['tipo_solicitacao']);
                        }
                        ?>
                        <a href="<?php echo $form_url; ?>" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 shadow-sm">
                            <span class="material-icons mr-2">description</span>
                            Ver Formulário
                        </a>
                    </div>
                </div>

                <!-- Cabeçalho do formulário -->
                <div class="mb-8 title-animation">
                    <div class="title-header rounded-lg p-6 text-white">
                        <h2 class="text-4xl font-bold mb-2"><?php echo $tituloFormulario; ?></h2>
                        <p class="text-white/80 flex items-center">
                            <i class='bx bx-file mr-2'></i>
                            Protocolo: #<?php echo $id; ?>
                        </p>
                    </div>
                </div>

                <?php
                // Carrega e exibe o conteúdo específico baseado no tipo de formulário
                if ($tipo === 'Parecer') {
                    require_once './includes/form_tebles/parecer.php';
                    exibirDetalhesParecer($conn, $id);
                } elseif ($tipo === 'PCD') {
                    require_once './includes/form_tebles/cartao.php';
                    exibirDetalhesCartao($conn, $id);
                } elseif ($tipo === 'JARI') {
                    require_once './includes/form_tebles/recursos.php';
                    exibirDetalhesRecurso($conn, $id);
                } elseif ($tipo === 'SAC') {
                    require_once './includes/form_tebles/sac.php';
                    exibirDetalhesSAC($conn, $id);
                } elseif ($tipo === 'DAT') {
                    require_once './includes/form_tebles/dat.php';
                    exibirDetalhesDAT($conn, $id);
                }
                ?>

                <div class="card">
                    <div class="card-footer p-4 flex justify-end space-x-3">
                        <!-- Botão de Exclusão -->
                        <button id="btnExcluir"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors duration-200 shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Excluir Formulário
                        </button>

                        <!-- Botão Concluir (existente) -->
                        <button id="btnConcluir"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors duration-200 shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Concluir Solicitação
                        </button>
                    </div>
                </div>

                <!-- Modal de Confirmação de Exclusão -->
                <div id="deleteModal"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 w-96 shadow-xl">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Confirmar Exclusão</h3>
                            <p class="text-gray-700 mt-2">Tem certeza que deseja excluir este formulário? Esta ação não
                                pode ser desfeita.</p>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button onclick="closeDeleteModal()"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                                Cancelar
                            </button>
                            <button onclick="confirmarExclusao()"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 flex items-center gap-2">
                                <i class="material-icons text-sm">delete</i>
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal de Loading/Erro -->
                <div id="statusModal"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 w-96 shadow-xl">
                        <!-- Loading State -->
                        <div id="loadingState" class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                            <p class="mt-4 text-gray-700">Processando solicitação...</p>
                        </div>

                        <!-- Error State -->
                        <div id="errorState" class="hidden">
                            <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293-1.293a1 1 0 001.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-red-700 font-medium" id="errorMessage">Erro ao processar
                                            solicitação</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button onclick="closeModal()"
                                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">
                                    Fechar
                                </button>
                            </div>
                        </div>

                        <!-- Success State -->
                        <div id="successState" class="hidden">
                            <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-green-700 font-medium">Solicitação concluída com sucesso!</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button onclick="closeModal()"
                                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
                                    Fechar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Removida a declaração duplicada de emailData e isProcessing
                    // pois agora estão no email_modal.php

                    function showModal(state = 'loading') {
                        const modal = document.getElementById('statusModal');
                        const loadingState = document.getElementById('loadingState');
                        const errorState = document.getElementById('errorState');
                        const successState = document.getElementById('successState');

                        // Reset states
                        loadingState.classList.add('hidden');
                        errorState.classList.add('hidden');
                        successState.classList.add('hidden');

                        // Show modal
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');

                        // Show correct state
                        switch (state) {
                            case 'loading':
                                loadingState.classList.remove('hidden');
                                break;
                            case 'error':
                                errorState.classList.remove('hidden');
                                break;
                            case 'success':
                                successState.classList.remove('hidden');
                                break;
                        }
                    }

                    function closeModal() {
                        const modal = document.getElementById('statusModal');
                        if (isProcessing) return; // Evita fechamento durante processamento

                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        if (document.getElementById('successState').classList.contains('hidden') === false) {
                            location.reload();
                        }
                    }

                    function closeEmailModal() {
                        const modal = document.getElementById('emailModal');
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }

                    function showEmailModal(data) {
                        emailData = data;
                        const modal = document.getElementById('emailModal');

                        // Preencher campos do preview
                        document.getElementById('previewTo').textContent = data.email;
                        document.getElementById('previewSubject').textContent = data.titulo;

                        // Preencher campos do formulário de edição
                        document.getElementById('emailTo').value = data.email;
                        document.getElementById('emailSubject').value = data.titulo;
                        document.getElementById('emailContent').value = data.conteudo;

                        updatePreview();
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');

                        // Esconder formulário de edição
                        document.getElementById('editForm').classList.add('hidden');
                    }

                    function showEditForm() {
                        document.getElementById('editForm').classList.remove('hidden');
                    }

                    function updatePreview() {
                        const emailPreview = document.getElementById('emailPreview');
                        const content = document.getElementById('emailContent').value;

                        emailPreview.innerHTML = content;

                        // Atualizar também o preview do cabeçalho
                        document.getElementById('previewTo').textContent = document.getElementById('emailTo').value;
                        document.getElementById('previewSubject').textContent = document.getElementById('emailSubject')
                            .value;
                    }

                    async function confirmarEnvio() {
                        if (isProcessing) return;

                        try {
                            isProcessing = true;
                            closeEmailModal();
                            showModal('loading');

                            const response = await fetch('concluir_formulario_ajax.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    id: '<?php echo $id; ?>',
                                    tipo: '<?php echo $tipo; ?>',
                                    email: document.getElementById('emailTo').value,
                                    assunto: document.getElementById('emailSubject').value,
                                    conteudo: document.getElementById('emailContent').value,
                                    confirmed: true
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                showModal('success');
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            } else {
                                document.getElementById('errorMessage').textContent = data.message;
                                showModal('error');
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            document.getElementById('errorMessage').textContent = error.message;
                            showModal('error');
                        } finally {
                            isProcessing = false;
                        }
                    }

                    // Event Listeners
                    document.addEventListener('DOMContentLoaded', function() {
                        const btnConcluir = document.getElementById('btnConcluir');
                        const documentoModal = document.getElementById('documentoModal');
                        const emailModal = document.getElementById('emailModal');
                        const statusModal = document.getElementById('statusModal');

                        if (btnConcluir) {
                            btnConcluir.addEventListener('click', async function() {
                                if (window.isProcessing) return; // Alterado para window.isProcessing
                                try {
                                    window.isProcessing = true; // Alterado para window.isProcessing
                                    showModal('loading');

                                    // Primeiro, solicita o preview do email
                                    const response = await fetch('concluir_formulario_ajax.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            id: '<?php echo $id; ?>',
                                            tipo: '<?php echo $tipo; ?>',
                                            preview: true
                                        })
                                    });

                                    const data = await response.json();

                                    // Esconder modal de status antes de mostrar o modal de email
                                    const statusModal = document.getElementById('statusModal');
                                    statusModal.classList.add('hidden');
                                    statusModal.classList.remove('flex');

                                    if (data.success && data.preview) {
                                        showEmailModal(data.preview);
                                    } else {
                                        throw new Error(data.message ||
                                            'Erro ao gerar preview do email');
                                    }
                                } catch (error) {
                                    console.error('Erro:', error);
                                    document.getElementById('errorMessage').textContent = error.message;
                                    showModal('error');
                                } finally {
                                    window.isProcessing = false; // Alterado para window.isProcessing
                                }
                            });
                        }

                        if (documentoModal) {
                            documentoModal.addEventListener('click', function(e) {
                                if (e.target === this && !isProcessing) fecharModal();
                            });
                        }

                        if (emailModal) {
                            emailModal.addEventListener('click', function(e) {
                                if (e.target === this && !isProcessing) closeEmailModal();
                            });
                        }

                        if (statusModal) {
                            statusModal.addEventListener('click', function(e) {
                                if (e.target === this && !isProcessing) closeModal();
                            });
                        }

                        const btnExcluir = document.getElementById('btnExcluir');
                        if (btnExcluir) {
                            btnExcluir.addEventListener('click', showDeleteModal);
                        }

                        // Fecha o modal de exclusão quando clica fora
                        const deleteModal = document.getElementById('deleteModal');
                        if (deleteModal) {
                            deleteModal.addEventListener('click', function(e) {
                                if (e.target === this && !window.isProcessing) closeDeleteModal();
                            });
                        }
                    });

                    const originalValues = {};

                    function toggleEdit(fieldId) {
                        const input = document.getElementById(fieldId);
                        const editBtn = input.parentElement.querySelector('.edit-button');
                        const saveBtn = input.parentElement.querySelector('.save-button');
                        const cancelBtn = input.parentElement.querySelector('.cancel-button');

                        // Guarda o valor original
                        if (!originalValues[fieldId]) {
                            originalValues[fieldId] = input.value;
                        }

                        // Ativa edição
                        input.readOnly = false;
                        input.classList.add('editing');
                        input.focus();

                        // Mostra/esconde botões
                        editBtn.classList.add('hidden');
                        saveBtn.classList.remove('hidden');
                        cancelBtn.classList.remove('hidden');
                    }

                    function saveField(fieldId) {
                        const input = document.getElementById(fieldId);
                        const newValue = input.value;
                        const fieldName = input.name;

                        // Aqui você deve implementar a chamada AJAX para salvar as alterações
                        fetch('salvar_campo.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    fieldName: fieldName,
                                    value: newValue,
                                    formId: '<?php echo $id; ?>',
                                    formType: '<?php echo $tipo; ?>'
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Atualiza o valor original
                                    originalValues[fieldId] = newValue;
                                    // Desativa edição
                                    finishEdit(fieldId);
                                    // Mostra mensagem de sucesso
                                    showToast('Campo atualizado com sucesso!', 'success');
                                } else {
                                    throw new Error(data.message || 'Erro ao salvar');
                                }
                            })
                            .catch(error => {
                                console.error('Erro:', error);
                                showToast(error.message, 'error');
                                cancelEdit(fieldId);
                            });
                    }

                    function cancelEdit(fieldId) {
                        const input = document.getElementById(fieldId);
                        input.value = originalValues[fieldId] || '';
                        finishEdit(fieldId);
                    }

                    function finishEdit(fieldId) {
                        const input = document.getElementById(fieldId);
                        const editBtn = input.parentElement.querySelector('.edit-button');
                        const saveBtn = input.parentElement.querySelector('.save-button');
                        const cancelBtn = input.parentElement.querySelector('.cancel-button');

                        input.readOnly = true;
                        input.classList.remove('editing');

                        editBtn.classList.remove('hidden');
                        saveBtn.classList.add('hidden');
                        cancelBtn.classList.add('hidden');
                    }

                    function showToast(message, type = 'success') {
                        const toast = document.createElement('div');
                        toast.className = `fixed bottom-4 right-4 p-4 rounded-lg shadow-lg ${
                            type === 'success' ? 'bg-green-500' : 'bg-red-500'
                        } text-white z-50`;
                        toast.textContent = message;

                        document.body.appendChild(toast);

                        setTimeout(() => {
                            toast.remove();
                        }, 3000);
                    }

                    // Funções para o modal de exclusão
                    function showDeleteModal() {
                        const modal = document.getElementById('deleteModal');
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }

                    function closeDeleteModal() {
                        const modal = document.getElementById('deleteModal');
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }

                    async function confirmarExclusao() {
                        if (window.isProcessing) return;

                        try {
                            window.isProcessing = true;
                            closeDeleteModal();
                            showModal('loading');

                            const response = await fetch('excluir_formulario_ajax.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    id: '<?php echo $id; ?>',
                                    tipo: '<?php echo $tipo; ?>'
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                showModal('success');
                                setTimeout(() => {
                                    window.location.href = 'formularios.php';
                                }, 2000);
                            } else {
                                throw new Error(data.message || 'Erro ao excluir formulário');
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            document.getElementById('errorMessage').textContent = error.message;
                            showModal('error');
                        } finally {
                            window.isProcessing = false;
                        }
                    }
                </script>

                <!-- Modal de Preview do Email -->
                <div id="emailModal"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 w-3/4 max-w-4xl max-h-[90vh] flex flex-col">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold">Confirmação de Envio</h3>
                            <button onclick="closeEmailModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="material-icons">close</i>
                            </button>
                        </div>

                        <!-- Preview do Email -->
                        <div class="flex-grow overflow-auto mb-4">
                            <div class="border rounded-md p-4 bg-gray-50">
                                <p class="text-sm text-gray-600 mb-2"><strong>Para:</strong> <span
                                        id="previewTo"></span></p>
                                <p class="text-sm text-gray-600 mb-4"><strong>Assunto:</strong> <span
                                        id="previewSubject"></span></p>
                                <div class="border-t pt-4">
                                    <div id="emailPreview" class="prose max-w-none"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Opções de Ação -->
                        <div class="flex flex-col items-center space-y-4">
                            <p class="text-gray-700">Precisa fazer alguma alteração no email?</p>
                            <div class="flex space-x-4">
                                <button onclick="showEditForm()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center">
                                    <i class="material-icons mr-2">edit</i> Editar Email
                                </button>
                                <button onclick="confirmarEnvio()"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center">
                                    <i class="material-icons mr-2">send</i> Enviar Email
                                </button>
                                <button onclick="closeEmailModal()"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 flex items-center">
                                    <i class="material-icons mr-2">close</i> Cancelar
                                </button>
                            </div>
                        </div>

                        <!-- Formulário de Edição (inicialmente escondido) -->
                        <div id="editForm" class="hidden mt-4 border-t pt-4">
                            <h4 class="text-lg font-semibold mb-4">Editar Email</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email do
                                        Destinatário:</label>
                                    <input type="email" id="emailTo"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Assunto:</label>
                                    <input type="text" id="emailSubject"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Mensagem:</label>
                                    <textarea id="emailContent" rows="6"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button onclick="updatePreview()"
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Atualizar Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">
                    &copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os direitos reservados.
                </p>
            </footer>
        </div>
    </div>

    <!-- Include do Modal de Email -->
    <?php require_once './includes/form_tebles/email_modal.php'; ?>

</body>

</html>