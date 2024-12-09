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
                        <a href="dashboard.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
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
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">SAC</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php echo $sacFormularios->num_rows; ?></p>
                                <p class="text-xs text-green-500 mt-2">
                                    <span class="material-icons text-xs align-middle">trending_up</span>
                                    Ativos
                                </p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <span class="material-icons text-blue-500">support_agent</span>
                            </div>
                        </div>
                    </div>

                    <!-- JARI Card -->
                    <div class="bg-white rounded-xl p-6 transform transition-all hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Defesas JARI</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php echo $jariFormularios->num_rows; ?></p>
                                <p class="text-xs text-yellow-500 mt-2">
                                    <span class="material-icons text-xs align-middle">pending</span>
                                    Em Análise
                                </p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-3">
                                <span class="material-icons text-yellow-500">gavel</span>
                            </div>
                        </div>
                    </div>

                    <!-- PCD Card -->
                    <div class="bg-white rounded-xl p-6 transform transition-all hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Cart��es PCD</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">
                                    <?php echo $pcdFormularios->num_rows; ?></p>
                                <p class="text-xs text-purple-500 mt-2">
                                    <span class="material-icons text-xs align-middle">card_membership</span>
                                    Solicitações
                                </p>
                            </div>
                            <div class="bg-purple-100 rounded-full p-3">
                                <span class="material-icons text-purple-500">accessible</span>
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
                                    while($form = $latestFormularios->fetch_assoc()):
                                    renderFormCard($form);
                                    endwhile;
                                    ?>
                                </div>

                                <!-- SAC Tab -->
                                <div x-show="activeTab === 'sac'" class="space-y-4">
                                    <?php 
                                    $sacFormularios = $conn->query("SELECT id, nome, data_submissao, 'sac' as tipo FROM sac ORDER BY data_submissao DESC LIMIT 5");
                                    while($form = $sacFormularios->fetch_assoc()):
                                    renderFormCard($form);
                                    endwhile;
                                    ?>
                                </div>

                                <!-- JARI Tab -->
                                <div x-show="activeTab === 'jari'" class="space-y-4">
                                    <?php 
                                    $jariFormularios = $conn->query("SELECT id, nome, data_submissao, 'jari' as tipo FROM solicitacoes_demutran ORDER BY data_submissao DESC LIMIT 5");
                                    while($form = $jariFormularios->fetch_assoc()):
                                    renderFormCard($form);
                                    endwhile;
                                    ?>
                                </div>

                                <!-- PCD Tab -->
                                <div x-show="activeTab === 'pcd'" class="space-y-4">
                                    <?php 
                                    $pcdFormularios = $conn->query("SELECT id, nome, data_submissao, 'pcd' as tipo FROM solicitacao_cartao ORDER BY data_submissao DESC LIMIT 5");
                                    while($form = $pcdFormularios->fetch_assoc()):
                                    renderFormCard($form);
                                    endwhile;
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
                                    while($form = $datFormularios->fetch_assoc()):
                                    renderFormCard($form);
                                    endwhile;
                                    ?>
                                </div>

                                <!-- Parecer Tab -->
                                <div x-show="activeTab === 'parecer'" class="space-y-4">
                                    <?php 
                                    $parecerFormularios = $conn->query("SELECT id, nome, data_submissao, 'parecer' as tipo FROM Parecer ORDER BY data_submissao DESC LIMIT 5");
                                    while($form = $parecerFormularios->fetch_assoc()):
                                    renderFormCard($form);
                                    endwhile;
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
                                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Service Distribution Chart -->
                    <div class="bg-white rounded-xl p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Distribuição de Serviços</h2>
                        <canvas id="servicesChart"></canvas>
                    </div>

                    <!-- Monthly Submissions Chart -->
                    <div class="bg-white rounded-xl p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Solicitações Mensais</h2>
                        <canvas id="monthlyChart"></canvas>
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

    <!-- Add Chart.js before closing body tag -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Obter dados para os gráficos
        const servicesData = {
            labels: ['SAC', 'JARI', 'PCD', 'DAT', 'Parecer'],
            datasets: [{
                data: [
                    <?php echo $sacFormularios->num_rows; ?>,
                    <?php echo $jariFormularios->num_rows; ?>,
                    <?php echo $pcdFormularios->num_rows; ?>,
                    <?php echo $dat4Formularios->num_rows; ?>,
                    <?php echo $parecerFormularios->num_rows; ?>
                ],
                backgroundColor: [
                    '#3B82F6', // Azul para SAC
                    '#F59E0B', // Amarelo para JARI
                    '#8B5CF6', // Roxo para PCD
                    '#EF4444', // Vermelho para DAT
                    '#10B981'  // Verde para Parecer
                ],
                borderWidth: 1
            }]
        };

        // Configuração do gráfico de pizza
        const servicesChart = new Chart(
            document.getElementById('servicesChart'),
            {
                type: 'doughnut',
                data: servicesData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Distribuição de Solicitações por Tipo'
                        }
                    }
                }
            }
        );

        // Dados para o gráfico mensal
        <?php
        $monthlyStats = $conn->query("
            SELECT 
                DATE_FORMAT(data_submissao, '%Y-%m') as month,
                COUNT(*) as total,
                SUM(CASE WHEN tipo = 'sac' THEN 1 ELSE 0 END) as sac,
                SUM(CASE WHEN tipo = 'jari' THEN 1 ELSE 0 END) as jari,
                SUM(CASE WHEN tipo = 'pcd' THEN 1 ELSE 0 END) as pcd,
                SUM(CASE WHEN tipo = 'dat' THEN 1 ELSE 0 END) as dat,
                SUM(CASE WHEN tipo = 'parecer' THEN 1 ELSE 0 END) as parecer
            FROM (
                SELECT data_submissao, 'sac' as tipo FROM sac
                UNION ALL
                SELECT data_submissao, 'jari' as tipo FROM solicitacoes_demutran
                UNION ALL
                SELECT data_submissao, 'pcd' as tipo FROM solicitacao_cartao
                UNION ALL
                SELECT data_submissao, 'dat' as tipo FROM DAT4
                UNION ALL
                SELECT data_submissao, 'parecer' as tipo FROM Parecer
            ) AS combined
            WHERE data_submissao >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(data_submissao, '%Y-%m')
            ORDER BY month ASC
        ");

        $labels = [];
        $sacData = [];
        $jariData = [];
        $pcdData = [];
        $datData = [];
        $parecerData = [];

        while ($row = $monthlyStats->fetch_assoc()) {
            $labels[] = date('M/Y', strtotime($row['month']));
            $sacData[] = $row['sac'];
            $jariData[] = $row['jari'];
            $pcdData[] = $row['pcd'];
            $datData[] = $row['dat'];
            $parecerData[] = $row['parecer'];
        }
        ?>

        // Configuração do gráfico de linha
        const monthlyChart = new Chart(
            document.getElementById('monthlyChart'),
            {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                            label: 'SAC',
                            data: <?php echo json_encode($sacData); ?>,
                            borderColor: '#3B82F6',
                            tension: 0.1
                        },
                        {
                            label: 'JARI',
                            data: <?php echo json_encode($jariData); ?>,
                            borderColor: '#F59E0B',
                            tension: 0.1
                        },
                        {
                            label: 'PCD',
                            data: <?php echo json_encode($pcdData); ?>,
                            borderColor: '#8B5CF6',
                            tension: 0.1
                        },
                        {
                            label: 'DAT',
                            data: <?php echo json_encode($datData); ?>,
                            borderColor: '#EF4444',
                            tension: 0.1
                        },
                        {
                            label: 'Parecer',
                            data: <?php echo json_encode($parecerData); ?>,
                            borderColor: '#10B981',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Solicitações nos Últimos 6 Meses'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            }
        );
    </script>
</body>

</html>