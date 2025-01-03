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
                    <div class="card-footer p-4 flex justify-end">
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
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
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
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        if (document.getElementById('successState').classList.contains('hidden') === false) {
                            location.reload();
                        }
                    }

                    document.getElementById('btnConcluir').addEventListener('click', async function() {
                        try {
                            showModal('loading');

                            const formData = {
                                id: '<?php echo $id; ?>',
                                tipo: '<?php echo $tipo; ?>',
                                email: '<?php echo $dados['email'] ?? ''; ?>',
                                nome: '<?php echo $dados['nome'] ?? ''; ?>'
                            };

                            const response = await fetch('concluir_formulario_ajax.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(formData)
                            });

                            const data = await response.json();

                            if (response.ok) {
                                showModal('success');
                                setTimeout(() => {
                                    closeModal();
                                    location.reload();
                                }, 2000);
                            } else {
                                document.getElementById('errorMessage').textContent = data.message ||
                                    'Erro ao processar a solicitação';
                                showModal('error');
                            }

                        } catch (error) {
                            console.error('Erro:', error);
                            document.getElementById('errorMessage').textContent = error.message;
                            showModal('error');
                        }
                    });
                </script>

            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">
                    &copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os direitos reservados.
                </p>
            </footer>
        </div>
    </div>
</body>

</html>