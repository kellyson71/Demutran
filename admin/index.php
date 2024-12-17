<?php
session_start();
include '../env/config.php';
include './includes/template.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

function obterUltimosFormularios($conn, $tabela) {
    $sql = "SELECT * FROM $tabela ORDER BY id DESC";
    return $conn->query($sql);
}

// Definir o número de registros por página
$registrosPorPagina = 5;

// Obter a página atual para JARI
$jariPaginaAtual = isset($_GET['jari_pagina']) ? (int)$_GET['jari_pagina'] : 1;
$jariOffset = ($jariPaginaAtual - 1) * $registrosPorPagina;

// Obter o total de registros JARI
$jariTotalRegistros = $conn->query("SELECT COUNT(*) AS total FROM solicitacoes_demutran")->fetch_assoc()['total'];
$jariTotalPaginas = ceil($jariTotalRegistros / $registrosPorPagina);

// Obter formulários JARI com paginação
$jariFormularios = $conn->query("SELECT * FROM solicitacoes_demutran ORDER BY id DESC LIMIT $registrosPorPagina OFFSET $jariOffset");

// Obter a página atual para PCD
$pcdPaginaAtual = isset($_GET['pcd_pagina']) ? (int)$_GET['pcd_pagina'] : 1;
$pcdOffset = ($pcdPaginaAtual - 1) * $registrosPorPagina;

// Obter o total de registros PCD
$pcdTotalRegistros = $conn->query("SELECT COUNT(*) AS total FROM solicitacao_cartao")->fetch_assoc()['total'];
$pcdTotalPaginas = ceil($pcdTotalRegistros / $registrosPorPagina);

// Obter formulários PCD com paginação
$pcdFormularios = $conn->query("SELECT * FROM solicitacao_cartao ORDER BY id DESC LIMIT $registrosPorPagina OFFSET $pcdOffset");

$sacFormularios = obterUltimosFormularios($conn, 'sac');
$dat4Formularios = obterUltimosFormularios($conn, 'DAT4');

// Obter o total de registros Parecer
$parecerTotalRegistros = $conn->query("SELECT COUNT(*) AS total FROM Parecer")->fetch_assoc()['total'];

$parecerFormularios = obterUltimosFormularios($conn, 'Parecer');

// Adicionar contagem separada para PCD e IDOSO
$sql_cartoes = "SELECT 
    SUM(CASE WHEN tipo_solicitacao = 'pcd' THEN 1 ELSE 0 END) as total_pcd,
    SUM(CASE WHEN tipo_solicitacao = 'idoso' THEN 1 ELSE 0 END) as total_idoso
FROM solicitacao_cartao";
$result_cartoes = $conn->query($sql_cartoes);
$cartoes = $result_cartoes->fetch_assoc();

// Adicionar queries para SAC e JARI no início do arquivo após as outras queries
$sql_sac = "SELECT 
    SUM(CASE WHEN tipo_contato = 'solicitacao' THEN 1 ELSE 0 END) as total_solicitacao,
    SUM(CASE WHEN tipo_contato = 'reclamacao' THEN 1 ELSE 0 END) as total_reclamacao,
    SUM(CASE WHEN tipo_contato = 'denuncia' THEN 1 ELSE 0 END) as total_denuncia
FROM sac";
$result_sac = $conn->query($sql_sac);
$sac_tipos = $result_sac->fetch_assoc();

$sql_jari = "SELECT 
    SUM(CASE WHEN tipo_solicitacao = 'apresentacao_condutor' THEN 1 ELSE 0 END) as total_apresentacao,
    SUM(CASE WHEN tipo_solicitacao = 'defesa_previa' THEN 1 ELSE 0 END) as total_defesa,
    SUM(CASE WHEN tipo_solicitacao = 'jari' THEN 1 ELSE 0 END) as total_jari
FROM solicitacoes_demutran";
$result_jari = $conn->query($sql_jari);
$jari_tipos = $result_jari->fetch_assoc();

function renderPagination($paginaAtual, $totalPaginas, $param) {
    $maxPaginasVisiveis = 8;
    $html = '';

    if ($paginaAtual > 1) {
        $html .= '<a href="?' . $param . '=' . ($paginaAtual - 1) . '" class="px-3 py-1 mx-1 bg-gray-200 rounded hover:bg-gray-300">Anterior</a>';
    }

    if ($totalPaginas <= $maxPaginasVisiveis) {
        for ($i = 1; $i <= $totalPaginas; $i++) {
            $html .= '<a href="?' . $param . '=' . $i . '" class="px-3 py-1 mx-1 ' . ($i == $paginaAtual ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300') . ' rounded">' . $i . '</a>';
        }
    } else {
        $inicio = max(1, $paginaAtual - 3);
        $fim = min($totalPaginas, $paginaAtual + 3);

        if ($inicio > 1) {
            $html .= '<a href="?' . $param . '=1" class="px-3 py-1 mx-1 bg-gray-200 hover:bg-gray-300 rounded">1</a>';
            if ($inicio > 2) {
                $html .= '<span class="px-3 py-1 mx-1">...</span>';
            }
        }

        for ($i = $inicio; $i <= $fim; $i++) {
            $html .= '<a href="?' . $param . '=' . $i . '" class="px-3 py-1 mx-1 ' . ($i == $paginaAtual ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300') . ' rounded">' . $i . '</a>';
        }

        if ($fim < $totalPaginas) {
            if ($fim < $totalPaginas - 1) {
                $html .= '<span class="px-3 py-1 mx-1">...</span>';
            }
            $html .= '<a href="?' . $param . '=' . $totalPaginas . '" class="px-3 py-1 mx-1 bg-gray-200 hover:bg-gray-300 rounded">' . $totalPaginas . '</a>';
        }
    }

    if ($paginaAtual < $totalPaginas) {
        $html .= '<a href="?' . $param . '=' . ($paginaAtual + 1) . '" class="px-3 py-1 mx-1 bg-gray-200 rounded hover:bg-gray-300">Próximo</a>';
    }

    return $html;
}

function renderFormCard($form) {
    ?>
<div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100">
    <div class="rounded-full bg-blue-100 p-2 mr-4">
        <span class="material-icons text-blue-500">description</span>
    </div>
    <div class="flex-1">
        <h3 class="text-sm font-medium"><?php echo htmlspecialchars($form['nome']); ?></h3>
        <p class="text-xs text-gray-500">
            <?php echo date('d/m/Y H:i', strtotime($form['data_submissao'])); ?>
        </p>
    </div>
    <a href="detalhes_formulario.php?id=<?php echo $form['id']; ?>&tipo=<?php echo strtoupper($form['tipo']); ?>"
        class="text-gray-400 hover:text-gray-600">
        <span class="material-icons">chevron_right</span>
    </a>
</div>
<?php
}

function renderEmptyState() {
    ?>
<div class="flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg">
    <span class="material-icons text-gray-400 text-5xl mb-4">inbox</span>
    <p class="text-gray-500 text-center mb-2">Nenhuma solicitação encontrada</p>
    <p class="text-sm text-gray-400 text-center">As solicitações aparecerão aqui quando forem enviadas.</p>
</div>
<?php
}

?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }" x-init="$refs.loading.classList.add('hidden')">

<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
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
    [x-cloak] {
        display: none;
    }
    </style>
</head>

<body class="bg-gray-100 font-roboto">
    <!-- Loader -->
    <div x-ref="loading" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
        <span class="material-icons animate-spin text-4xl text-blue-600">autorenew</span>
    </div>

    <!-- Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 flex flex-col h-full">
                <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                <?php echo getSidebarHtml('dashboard'); ?>
                <div class="mt-6">
                    <a href="logout.php" class="flex items-center p-2 text-red-600 hover:bg-red-50 rounded">
                        <span class="material-icons">logout</span>
                        <span class="ml-3">Sair</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar -->
        <div x-show="open" @click.away="open = false" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden">
            <aside class="w-64 bg-white h-full shadow-md">
                <div class="p-6">
                    <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                    <nav class="space-y-2">
                        <a href="index.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                            <span class="material-icons">dashboard</span>
                            <span class="ml-3 font-semibold">Dashboard</span>
                        </a>
                        <a href="formularios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">assignment</span>
                            <span class="ml-3">Formulários</span>
                        </a>
                        <a href="gerenciar_noticias.php"
                            class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">article</span>
                            <span class="ml-3">Notícias</span>
                        </a>
                        <a href="usuarios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">people</span>
                            <span class="ml-3">Usuários</span>
                        </a>
                        <a href="perfil.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">person</span>
                            <span class="ml-3">Perfil</span>
                        </a>
                        <a href="gerenciar_noticias.php"
                            class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">article</span>
                            <span class="ml-3">Gerenciar Notícias</span>
                        </a>
                        <a href="analytics.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">analytics</span>
                            <span class="ml-3">Análise de Dados</span>
                        </a>
                        <a href="logout.php" class="flex items-center p-2 text-red-600 hover:bg-red-50 rounded">
                            <span class="material-icons">logout</span>
                            <span class="ml-3">Sair</span>
                        </a>
                    </nav>
                </div>
            </aside>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php 
            $topbarHtml = getTopbarHtml('Dashboard', $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Welcome Section -->
                <div class="relative overflow-hidden bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-8 mb-6">
                    <div class="relative z-10">
                        <h1 class="text-3xl font-bold text-white mb-2">Olá, <?php echo $_SESSION['usuario_nome']; ?>!
                        </h1>
                        <p class="text-blue-100">Dashboard Administrativa - Gestão de Formulários</p>
                    </div>
                    <div class="absolute right-0 top-0 transform translate-x-1/4 -translate-y-1/4">
                        <span class="material-icons text-blue-400 opacity-50" style="font-size: 196px;">dashboard</span>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                    <!-- SAC Card -->
                    <div class="bg-white rounded-xl p-6 transform transition-all hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">SAC</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php echo ($sac_tipos['total_solicitacao'] + $sac_tipos['total_reclamacao'] + $sac_tipos['total_denuncia']); ?>
                                </p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <span class="material-icons text-blue-500">support_agent</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center">
                                    <span class="material-icons text-green-500 text-sm mr-1">question_answer</span>
                                    Solicitações
                                </span>
                                <span class="font-medium"><?php echo $sac_tipos['total_solicitacao']; ?></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center">
                                    <span class="material-icons text-orange-500 text-sm mr-1">warning</span>
                                    Reclamações
                                </span>
                                <span class="font-medium"><?php echo $sac_tipos['total_reclamacao']; ?></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center">
                                    <span class="material-icons text-red-500 text-sm mr-1">report</span>
                                    Denúncias
                                </span>
                                <span class="font-medium"><?php echo $sac_tipos['total_denuncia']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- JARI Card -->
                    <div class="bg-white rounded-xl p-6 transform transition-all hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Defesas JARI</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php echo ($jari_tipos['total_apresentacao'] + $jari_tipos['total_defesa'] + $jari_tipos['total_jari']); ?>
                                </p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-3">
                                <span class="material-icons text-yellow-500">gavel</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center">
                                    <span class="material-icons text-indigo-500 text-sm mr-1">person_pin</span>
                                    Apresentação
                                </span>
                                <span class="font-medium"><?php echo $jari_tipos['total_apresentacao']; ?></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center">
                                    <span class="material-icons text-purple-500 text-sm mr-1">security</span>
                                    Defesa Prévia
                                </span>
                                <span class="font-medium"><?php echo $jari_tipos['total_defesa']; ?></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center">
                                    <span class="material-icons text-yellow-500 text-sm mr-1">balance</span>
                                    Recurso JARI
                                </span>
                                <span class="font-medium"><?php echo $jari_tipos['total_jari']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- PCD/IDOSO Card -->
                    <div class="bg-white rounded-xl p-6 transform transition-all hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Cartões Especiais</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php echo ($cartoes['total_pcd'] + $cartoes['total_idoso']); ?>
                                </p>
                            </div>
                            <div class="bg-purple-100 rounded-full p-3">
                                <span class="material-icons text-purple-500">badge</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center">
                                    <span class="material-icons text-blue-500 text-sm mr-1">accessible</span>
                                    PCD
                                </span>
                                <span class="font-medium"><?php echo $cartoes['total_pcd']; ?></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center">
                                    <span class="material-icons text-green-500 text-sm mr-1">elderly</span>
                                    IDOSO
                                </span>
                                <span class="font-medium"><?php echo $cartoes['total_idoso']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- DAT Card -->
                    <div class="bg-white rounded-xl p-6 transform transition-all hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">DAT</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php echo $dat4Formularios->num_rows; ?></p>
                                <p class="text-xs text-red-500 mt-2">
                                    <span class="material-icons text-xs align-middle">report</span>
                                    Acidentes
                                </p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3">
                                <span class="material-icons text-red-500">car_crash</span>
                            </div>
                        </div>
                    </div>

                    <!-- Parecer Card -->
                    <div class="bg-white rounded-xl p-6 transform transition-all hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Parecer</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php echo $parecerFormularios->num_rows; ?></p>
                                <p class="text-xs text-green-500 mt-2">
                                    <span class="material-icons text-xs align-middle">description</span>
                                    Pareceres
                                </p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <span class="material-icons text-green-500">assignment</span>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Recent Activity Section with Tabs -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Latest Forms with Tabs -->
                    <div class="bg-white rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-800">Últimas Solicitações</h2>
                            <a href="formularios.php" class="text-sm text-blue-500 hover:text-blue-700">Ver todos</a>
                        </div>

                        <!-- Tabs -->
                        <div x-data="{ activeTab: 'latest' }" class="mb-4">
                            <div class="flex space-x-4 border-b">
                                <button @click="activeTab = 'latest'"
                                    :class="{ 'border-b-2 border-blue-500': activeTab === 'latest' }"
                                    class="px-4 py-2 text-sm font-medium">Recentes</button>
                                <button @click="activeTab = 'sac'"
                                    :class="{ 'border-b-2 border-blue-500': activeTab === 'sac' }"
                                    class="px-4 py-2 text-sm font-medium">SAC</button>
                                <button @click="activeTab = 'jari'"
                                    :class="{ 'border-b-2 border-blue-500': activeTab === 'jari' }"
                                    class="px-4 py-2 text-sm font-medium">JARI</button>
                                <button @click="activeTab = 'pcd'"
                                    :class="{ 'border-b-2 border-blue-500': activeTab === 'pcd' }"
                                    class="px-4 py-2 text-sm font-medium">PCD</button>
                                <button @click="activeTab = 'dat'"
                                    :class="{ 'border-b-2 border-blue-500': activeTab === 'dat' }"
                                    class="px-4 py-2 text-sm font-medium">DAT</button>
                                <button @click="activeTab = 'parecer'"
                                    :class="{ 'border-b-2 border-blue-500': activeTab === 'parecer' }"
                                    class="px-4 py-2 text-sm font-medium">Parecer</button>
                            </div>

                            <!-- Tab Contents -->
                            <div class="space-y-4 mt-4">
                                <!-- Latest Tab -->
                                <div x-show="activeTab === 'latest'" class="space-y-4">
                                    <?php 
                                    $latestFormularios = $conn->query("
                                        (SELECT id, nome, data_submissao, 'sac' as tipo FROM sac)
                                        UNION
                                        (SELECT id, nome, data_submissao, 'jari' as tipo FROM solicitacoes_demutran)
                                        UNION
                                        (SELECT id, nome, data_submissao, 'pcd' as tipo FROM solicitacao_cartao)
                                        UNION
                                        (SELECT id, nome, data_submissao, 'parecer' as tipo FROM Parecer)
                                        ORDER BY data_submissao DESC LIMIT 5
                                    ");
                                    if ($latestFormularios->num_rows > 0) {
                                        while($form = $latestFormularios->fetch_assoc()):
                                            renderFormCard($form);
                                        endwhile;
                                    } else {
                                        renderEmptyState();
                                    }
                                    ?>
                                </div>

                                <!-- SAC Tab -->
                                <div x-show="activeTab === 'sac'" class="space-y-4">
                                    <?php 
                                    $sacFormularios = $conn->query("SELECT id, nome, data_submissao, 'sac' as tipo FROM sac ORDER BY data_submissao DESC LIMIT 5");
                                    if ($sacFormularios->num_rows > 0) {
                                        while($form = $sacFormularios->fetch_assoc()):
                                            renderFormCard($form);
                                        endwhile;
                                    } else {
                                        renderEmptyState();
                                    }
                                    ?>
                                </div>

                                <!-- JARI Tab -->
                                <div x-show="activeTab === 'jari'" class="space-y-4">
                                    <?php 
                                    $jariFormularios = $conn->query("SELECT id, nome, data_submissao, 'jari' as tipo FROM solicitacoes_demutran ORDER BY data_submissao DESC LIMIT 5");
                                    if ($jariFormularios->num_rows > 0) {
                                        while($form = $jariFormularios->fetch_assoc()):
                                            renderFormCard($form);
                                        endwhile;
                                    } else {
                                        renderEmptyState();
                                    }
                                    ?>
                                </div>

                                <!-- PCD Tab -->
                                <div x-show="activeTab === 'pcd'" class="space-y-4">
                                    <?php 
                                    $pcdFormularios = $conn->query("SELECT id, nome, data_submissao, 'pcd' as tipo FROM solicitacao_cartao ORDER BY data_submissao DESC LIMIT 5");
                                    if ($pcdFormularios->num_rows > 0) {
                                        while($form = $pcdFormularios->fetch_assoc()):
                                            renderFormCard($form);
                                        endwhile;
                                    } else {
                                        renderEmptyState();
                                    }
                                    ?>
                                </div>

                                <!-- DAT Tab -->
                                <div x-show="activeTab === 'dat'" class="space-y-4">
                                    <?php 
                                    $datFormularios = $conn->query("
                                        SELECT 
                                            d4.id, 
                                            d1.nome, 
                                            d4.data_submissao, 
                                            'dat' as tipo 
                                        FROM DAT4 d4
                                        JOIN DAT1 d1 ON d4.token = d1.token 
                                        ORDER BY d4.data_submissao DESC 
                                        LIMIT 5
                                    ");
                                    if ($datFormularios->num_rows > 0) {
                                        while($form = $datFormularios->fetch_assoc()):
                                            renderFormCard($form);
                                        endwhile;
                                    } else {
                                        renderEmptyState();
                                    }
                                    ?>
                                </div>

                                <!-- Parecer Tab -->
                                <div x-show="activeTab === 'parecer'" class="space-y-4">
                                    <?php 
                                    $parecerFormularios = $conn->query("SELECT id, nome, data_submissao, 'parecer' as tipo FROM Parecer ORDER BY data_submissao DESC LIMIT 5");
                                    if ($parecerFormularios->num_rows > 0) {
                                        while($form = $parecerFormularios->fetch_assoc()):
                                            renderFormCard($form);
                                        endwhile;
                                    } else {
                                        renderEmptyState();
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl p-6 h-fit">
                        <!-- Added h-fit to match height -->
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Ações Rápidas</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <!-- Changed to 3 columns on larger screens -->
                            <a href="formularios.php?tipo=JARI"
                                class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="material-icons text-yellow-500 mb-2">gavel</span>
                                <span class="text-sm font-medium text-center">Defesas JARI</span>
                            </a>
                            <a href="formularios.php?tipo=PCD"
                                class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="material-icons text-purple-500 mb-2">accessible</span>
                                <span class="text-sm font-medium text-center">Cartões PCD</span>
                            </a>
                            <a href="formularios.php?tipo=DAT"
                                class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="material-icons text-red-500 mb-2">car_crash</span>
                                <span class="text-sm font-medium text-center">Registrar DAT</span>
                            </a>
                            <a href="formularios.php?tipo=SAC"
                                class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="material-icons text-blue-500 mb-2">support_agent</span>
                                <span class="text-sm font-medium text-center">SAC</span>
                            </a>
                            <a href="formularios.php?tipo=PARECER"
                                class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="material-icons text-green-500 mb-2">assignment</span>
                                <span class="text-sm font-medium text-center">Parecer</span>
                            </a>
                            <a href="gerenciar_noticias.php"
                                class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="material-icons text-indigo-500 mb-2">article</span>
                                <span class="text-sm font-medium text-center">Notícias</span>
                            </a>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">&copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os
                    direitos reservados.</p>
            </footer>
        </div>
    </div>


</body>

</html>