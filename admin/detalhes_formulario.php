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