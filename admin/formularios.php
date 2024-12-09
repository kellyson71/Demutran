<?php
session_start();
include '../env/config.php';
include './includes/template.php';  // Adicionar include do template

// Atualizar view_mode se houver uma mudança via GET
if (isset($_GET['view'])) {
    $_SESSION['view_mode'] = $_GET['view'];
}

// Usar o modo de visualização da sessão ou o padrão 'grid'
$view_mode = $_SESSION['view_mode'] ?? 'grid';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}



$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

function obterSubmissoesPaginadas($conn, $tabela, $limite, $offset) {
    $sql = "SELECT * FROM $tabela ORDER BY id DESC LIMIT $limite OFFSET $offset";
    return $conn->query($sql);
}

function getFormularioStyle($tipo_formulario) {
    $styles = [
        'DAT' => ['bg' => 'bg-blue-100', 'border' => 'border-blue-500', 'text' => 'text-blue-800', 'icon' => 'directions_car', 'gradient' => 'from-blue-500 to-blue-600'],
        'JARI' => ['bg' => 'bg-red-100', 'border' => 'border-red-500', 'text' => 'text-red-800', 'icon' => 'gavel', 'gradient' => 'from-red-500 to-red-600'],
        'PCD' => ['bg' => 'bg-green-100', 'border' => 'border-green-500', 'text' => 'text-green-800', 'icon' => 'accessible', 'gradient' => 'from-green-500 to-green-600'],
        'SAC' => ['bg' => 'bg-purple-100', 'border' => 'border-purple-500', 'text' => 'text-purple-800', 'icon' => 'email', 'gradient' => 'from-purple-500 to-purple-600'],
        'Parecer' => ['bg' => 'bg-yellow-100', 'border' => 'border-yellow-500', 'text' => 'text-yellow-800', 'icon' => 'description', 'gradient' => 'from-yellow-500 to-yellow-600'],
    ];

    return $styles[$tipo_formulario] ?? ['bg' => 'bg-gray-100', 'border' => 'border-gray-500', 'text' => 'text-gray-800', 'icon' => 'help', 'gradient' => 'from-gray-500 to-gray-600'];
}

// Add this helper function near the top with other functions
function safeString($value) {
    return htmlspecialchars($value ?? 'Não informado', ENT_QUOTES, 'UTF-8');
}

// Variáveis de paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Obter submissões paginadas de cada tabela
$sac = obterSubmissoesPaginadas($conn, 'sac', $limite, $offset);
$jari = obterSubmissoesPaginadas($conn, 'solicitacoes_demutran', $limite, $offset);
$pcd = obterSubmissoesPaginadas($conn, 'solicitacao_cartao', $limite, $offset);
$dat = obterSubmissoesPaginadas($conn, 'DAT4', $limite, $offset);

// Combinar todas as submissões em um array
$submissoes = [];

while ($row = $sac->fetch_assoc()) {
    $row['tipo'] = 'SAC';
    $submissoes[] = $row;
}
while ($row = $jari->fetch_assoc()) {
    $row['tipo'] = 'JARI';
    $submissoes[] = $row;
}
while ($row = $pcd->fetch_assoc()) {
    $row['tipo'] = 'PCD';
    $submissoes[] = $row;
}
// Processamento das submissões de 'DAT'
while ($row = $dat->fetch_assoc()) {
    $row['tipo'] = 'DAT';
    // Buscar 'nome' na tabela 'DAT1' usando o 'token'
    $token = $conn->real_escape_string($row['token']);
    $sql_nome = "SELECT nome FROM DAT1 WHERE token = '$token' LIMIT 1";
    $result_nome = $conn->query($sql_nome);
    if ($result_nome->num_rows > 0) {
        $row_nome = $result_nome->fetch_assoc();
        $row['nome'] = $row_nome['nome'];
    } else {
        $row['nome'] = 'Nome não encontrado';
    }
    $submissoes[] = $row;
}


// Obter parâmetros de pesquisa e filtro
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$tipo_filter = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Definir os tipos e tabelas correspondentes
$tipos = [
    'SAC' => 'sac',
    'JARI' => 'solicitacoes_demutran',
    'PCD' => 'solicitacao_cartao',
    'DAT' => 'DAT4',
    'Parecer' => 'Parecer'  // New table
];

// Inicializar array de submissões
$submissoes = [];

// Definir um limite maior para buscar mais registros para a filtragem
$fetch_limit = 100;

// Obter submissões com base na pesquisa e filtros
foreach ($tipos as $tipo => $tabela) {
    if (empty($tipo_filter) || $tipo_filter == $tipo) {
        $result = obterSubmissoesPaginadas($conn, $tabela, $fetch_limit, 0);
        while ($row = $result->fetch_assoc()) {
            $row['tipo'] = $tipo;

            // Para 'DAT', buscar 'nome' na tabela 'DAT1'
            if ($tipo == 'DAT') {
                $token = $conn->real_escape_string($row['token']);
                $sql_nome = "SELECT nome FROM DAT1 WHERE token = '$token' LIMIT 1";
                $result_nome = $conn->query($sql_nome);
                if ($result_nome->num_rows > 0) {
                    $row_nome = $result_nome->fetch_assoc();
                    $row['nome'] = $row_nome['nome'];
                } else {
                    $row['nome'] = 'Nome não encontrado';
                }
            }

            // No special processing needed for 'Parecer'

            // Aplicar filtro de pesquisa
            if (!empty($search)) {
                if (isset($row['nome']) && stripos($row['nome'], $search) === false) {
                    continue; // Ignorar se não corresponder à pesquisa
                }
            }

            $submissoes[] = $row;
        }
    }
}

// Ordenar submissões por data
usort($submissoes, function($a, $b) {
    return strtotime($b['data_submissao']) - strtotime($a['data_submissao']);
});

// Paginação
$total_submissoes = count($submissoes);
$per_page = 10;
$total_pages = ceil($total_submissoes / $per_page);
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$start = ($pagina - 1) * $per_page;
$submissoes_pagina = array_slice($submissoes, $start, $per_page);

?>

<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false, viewMode: '<?php echo $view_mode; ?>' }" x-init="$refs.loading.classList.add('hidden');
    $watch('viewMode', value => {
        fetch('?view=' + value);
        localStorage.setItem('preferredView', value);
    })">

<head>
    <meta charset="UTF-8">
    <title>Formulários Recebidos</title>
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

    <!-- Script para filtros -->
    <script>
    function filtrar(tipo) {
        var cards = document.querySelectorAll('.form-card');
        cards.forEach(function(card) {
            if (tipo === 'todos' || card.dataset.tipo === tipo) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }
    </script>
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
                <?php echo getSidebarHtml('formularios'); ?>
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
                        <a href="dashboard.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">dashboard</span>
                            <span class="ml-3">Dashboard</span>
                        </a>
                        <a href="formularios.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                            <span class="material-icons">assignment</span>
                            <span class="ml-3 font-semibold">Formulários</span>
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
            $topbarHtml = getTopbarHtml('Formulários Recebidos', $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Filtros -->
                <!-- Barra de Pesquisa com Filtros -->
                <div class="mb-8">
                    <div class="max-w-4xl mx-auto">
                        <form method="GET" action="" class="relative">
                            <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-4 md:space-y-0">
                                <!-- Search Input Group -->
                                <div class="flex-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-gray-400">search</span>
                                    </div>
                                    <input type="text" name="search" placeholder="Pesquisar formulários..."
                                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm text-gray-600 placeholder-gray-400 transition-all duration-200"
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                </div>

                                <!-- Select Filter Group -->
                                <div class="relative w-full md:w-64">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-gray-400">filter_list</span>
                                    </div>
                                    <select name="tipo"
                                        class="block w-full pl-10 pr-10 py-2.5 border border-gray-200 rounded-lg appearance-none bg-white shadow-sm text-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer transition-all duration-200">
                                        <option value="">Todos os tipos</option>
                                        <option value="SAC"
                                            <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'SAC') ? 'selected' : ''; ?>>
                                            SAC</option>
                                        <option value="JARI"
                                            <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'JARI') ? 'selected' : ''; ?>>
                                            JARI</option>
                                        <option value="PCD"
                                            <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'PCD') ? 'selected' : ''; ?>>
                                            PCD</option>
                                        <option value="DAT"
                                            <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'DAT') ? 'selected' : ''; ?>>
                                            DAT</option>
                                        <option value="Parecer"
                                            <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'Parecer') ? 'selected' : ''; ?>>
                                            Parecer</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <span class="material-icons text-gray-400">expand_more</span>
                                    </div>
                                </div>

                                <!-- Search Button -->
                                <button type="submit"
                                    class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <span class="material-icons mr-2">search</span>
                                    Buscar
                                </button>
                            </div>

                            <!-- Active Filters (if any) -->
                            <?php if (!empty($_GET['search']) || !empty($_GET['tipo'])): ?>
                            <div class="mt-4 flex items-center space-x-2">
                                <span class="text-sm text-gray-500">Filtros ativos:</span>
                                <?php if (!empty($_GET['search'])): ?>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                    "<?php echo htmlspecialchars($_GET['search']); ?>"
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['search' => ''])); ?>"
                                        class="ml-2 text-blue-600 hover:text-blue-800">
                                        <span class="material-icons text-sm">close</span>
                                    </a>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($_GET['tipo'])): ?>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800">
                                    Tipo: <?php echo htmlspecialchars($_GET['tipo']); ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['tipo' => ''])); ?>"
                                        class="ml-2 text-gray-600 hover:text-gray-800">
                                        <span class="material-icons text-sm">close</span>
                                    </a>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="flex justify-end mb-4">
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                        <button @click="viewMode = 'grid'" type="button"
                            :class="{ 'bg-blue-100 text-blue-600': viewMode === 'grid', 'hover:bg-gray-50': viewMode !== 'grid' }"
                            class="inline-flex items-center px-3 py-1.5 rounded-md transition-all duration-200">
                            <span class="material-icons text-lg mr-1">grid_view</span>
                            Grade
                        </button>
                        <button @click="viewMode = 'list'" type="button"
                            :class="{ 'bg-blue-100 text-blue-600': viewMode === 'list', 'hover:bg-gray-50': viewMode !== 'list' }"
                            class="inline-flex items-center px-3 py-1.5 rounded-md transition-all duration-200">
                            <span class="material-icons text-lg mr-1">view_list</span>
                            Lista
                        </button>
                    </div>
                </div>

                <!-- Cards Container -->
                <div x-bind:class="{
                    'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4': viewMode === 'grid',
                    'space-y-3': viewMode === 'list'
                }">
                    <?php foreach ($submissoes_pagina as $item): ?>
                    <?php $style = getFormularioStyle($item['tipo']); ?>

                    <div class="form-card bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200"
                        x-bind:class="{
                            'border-l-4 <?php echo $style['border']; ?>': viewMode === 'grid',
                            'border border-gray-200 hover:border-<?php echo explode('-', $style['border'])[1]; ?>': viewMode === 'list'
                        }">
                        <!-- Grid View -->
                        <template x-if="viewMode === 'grid'">
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <span
                                            class="material-icons <?php echo $style['text']; ?> mr-2"><?php echo $style['icon']; ?></span>
                                        <h3 class="text-lg font-semibold text-gray-800"><?php echo $item['tipo']; ?>
                                        </h3>
                                    </div>
                                    <span class="text-sm text-gray-500">#<?php echo $item['id']; ?></span>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                        <span class="text-sm text-gray-600">Nome</span>
                                        <span
                                            class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($item['nome']); ?></span>
                                    </div>

                                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                        <span class="text-sm text-gray-600">Data</span>
                                        <span
                                            class="text-sm font-medium text-gray-800"><?php echo date('d/m/Y', strtotime($item['data_submissao'])); ?></span>
                                    </div>

                                    <?php if($item['tipo'] === 'Parecer'): ?>
                                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                        <span class="text-sm text-gray-600">Local</span>
                                        <span
                                            class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($item['local']); ?></span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                        <span class="text-sm text-gray-600">Protocolo</span>
                                        <span
                                            class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($item['protocolo']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-4 pt-2">
                                    <a href="detalhes_formulario.php?id=<?php echo $item['id']; ?>&tipo=<?php echo $item['tipo']; ?>"
                                        class="inline-flex items-center justify-center w-full px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                        <span class="material-icons text-sm mr-2">visibility</span>
                                        Visualizar detalhes
                                    </a>
                                </div>
                            </div>
                        </template>

                        <!-- List View -->
                        <template x-if="viewMode === 'list'">
                            <div class="flex items-center p-4">
                                <div class="flex items-center justify-between w-full">
                                    <!-- Left section: Icon and Basic Info -->
                                    <div class="flex items-center space-x-4">
                                        <div class="<?php echo $style['bg']; ?> p-2 rounded-lg">
                                            <span
                                                class="material-icons <?php echo $style['text']; ?>"><?php echo $style['icon']; ?></span>
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <h3 class="font-semibold text-gray-800">
                                                    <?php echo safeString($item['nome'] ?? ($item['solicitante'] ?? '')); ?>
                                                </h3>
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full <?php echo $style['bg']; ?> <?php echo $style['text']; ?>"><?php echo $item['tipo']; ?></span>
                                            </div>
                                            <p class="text-sm text-gray-500">
                                                ID: #<?php echo $item['id']; ?> •
                                                Submetido em:
                                                <?php echo date('d/m/Y', strtotime($item['data_submissao'])); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Right section: Status and Action -->
                                    <div class="flex items-center space-x-4">
                                        <?php if($item['tipo'] === 'Parecer'): ?>
                                        <div class="hidden md:block text-right">
                                            <p class="text-sm text-gray-600">Local:
                                                <?php echo safeString($item['local']); ?></p>
                                            <p class="text-sm text-gray-600">Protocolo:
                                                <?php echo safeString($item['protocolo']); ?></p>
                                        </div>
                                        <?php endif; ?>

                                        <a href="detalhes_formulario.php?id=<?php echo $item['id']; ?>&tipo=<?php echo $item['tipo']; ?>"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                            <span class="material-icons text-sm mr-2">visibility</span>
                                            Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <?php endforeach; ?>
                </div>


                <!-- Paginação -->
                <div class="flex justify-center mt-6 space-x-2">
                    <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?php echo $pagina - 1; ?>&search=<?php echo urlencode($search); ?>&tipo=<?php echo urlencode($tipo_filter); ?>"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Anterior</a>
                    <?php endif; ?>
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded">Página <?php echo $pagina; ?> de
                        <?php echo $total_pages; ?></span>
                    <?php if ($pagina < $total_pages): ?>
                    <a href="?pagina=<?php echo $pagina + 1; ?>&search=<?php echo urlencode($search); ?>&tipo=<?php echo urlencode($tipo_filter); ?>"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Próxima</a>
                    <?php endif; ?>
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