<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

function contarNotificacoesNaoLidas($conn) {
    $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE lida = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

function obterSubmissoesPaginadas($conn, $tabela, $limite, $offset) {
    $sql = "SELECT * FROM $tabela ORDER BY id DESC LIMIT $limite OFFSET $offset";
    return $conn->query($sql);
}

function getFormularioStyle($tipo_formulario) {
    $styles = [
        'DAT' => ['bg' => 'bg-blue-100', 'border' => 'border-blue-500', 'text' => 'text-blue-800', 'icon' => 'directions_car'],
        'JARI' => ['bg' => 'bg-yellow-100', 'border' => 'border-yellow-500', 'text' => 'text-yellow-800', 'icon' => 'gavel'],
        'PCD' => ['bg' => 'bg-green-100', 'border' => 'border-green-500', 'text' => 'text-green-800', 'icon' => 'accessible'],
        'SAC' => ['bg' => 'bg-purple-100', 'border' => 'border-purple-500', 'text' => 'text-purple-800', 'icon' => 'email'],
    ];

    return $styles[$tipo_formulario] ?? ['bg' => 'bg-gray-100', 'border' => 'border-gray-500', 'text' => 'text-gray-800', 'icon' => 'help'];
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
    'DAT' => 'DAT4'
];

// Inicializar array de submissões
$submissoes = [];

// Definir um limite maior para buscar mais registros para a filtragem
$fetch_limit = 100;

// Obter submissões com base na pesquisa e filtros
foreach ($tipos as $tipo => $tabela) {
    if (empty($tipo_filter) || $tipo_filter == $tipo) {
        $result = obterSubmissoesPaginadas($conn, $tabela, $fetch_limit, 0, $search, $tipo);
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

            // Aplicar filtro de pesquisa
            if (!empty($search)) {
                if (stripos($row['nome'], $search) === false) {
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
<html lang="pt-BR" x-data="{ open: false }" x-init="$refs.loading.classList.add('hidden')">
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
        [x-cloak] { display: none; }
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
                <nav class="space-y-2 flex-1">
                    <a href="dashboard.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">dashboard</span>
                        <span class="ml-3">Dashboard</span>
                    </a>
                    <a href="formularios.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                        <span class="material-icons">assignment</span>
                        <span class="ml-3 font-semibold">Formulários</span>
                    </a>
                    <a href="gerenciar_noticias.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">article</span>
                        <span class="ml-3">Notícias</span>
                    </a>
                    <a href="usuarios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">people</span>
                        <span class="ml-3">Usuários</span>
                    </a>
                </nav>
                <div class="mt-6">
                    <a href="logout.php" class="flex items-center p-2 text-red-600 hover:bg-red-50 rounded">
                        <span class="material-icons">logout</span>
                        <span class="ml-3">Sair</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar -->
        <div x-show="open" @click.away="open = false" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden">
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
                        <a href="gerenciar_noticias.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
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
            <!-- Topbar -->
            <header class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <!-- Mobile Menu Button -->
                    <button @click="open = !open" class="md:hidden focus:outline-none">
                        <span class="material-icons">menu</span>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800">Formulários Recebidos</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative focus:outline-none">
                            <span class="material-icons text-gray-700">notifications</span>
                            <?php if ($notificacoesNaoLidas > 0): ?>
                                <span class="absolute top-0 right-0 bg-red-600 text-white rounded-full px-1 text-xs"><?php echo $notificacoesNaoLidas; ?></span>
                            <?php endif; ?>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50">
                            <div class="p-4 border-b text-gray-700 font-bold">Notificações</div>
                            <ul>
                                <?php
                                $notificacoes = obterSubmissoesPaginadas($conn, 'notificacoes', 5, 0);
                                while($notificacao = $notificacoes->fetch_assoc()):
                                ?>
                                    <li class="p-4 border-b hover:bg-gray-50">
                                        <a href="#" class="block">
                                            <p class="font-medium text-gray-800"><?php echo $notificacao['titulo']; ?></p>
                                            <p class="text-sm text-gray-600"><?php echo $notificacao['mensagem']; ?></p>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- User Profile -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center focus:outline-none">
                            <img src="avatar.png" alt="Avatar" class="w-8 h-8 rounded-full">
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-50">
                            <div class="p-4 border-b text-gray-700 font-bold"><?php echo $_SESSION['usuario_nome']; ?></div>
                            <ul>
                                <li class="p-4 hover:bg-gray-50">
                                    <a href="perfil.php" class="block text-gray-700">Perfil</a>
                                </li>
                                <li class="p-4 hover:bg-gray-50">
                                    <a href="logout.php" class="block text-red-600">Sair</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Filtros -->
                <!-- Barra de Pesquisa com Filtros -->
                <div class="mb-6">
                    <form method="GET" action="" class="flex items-center space-x-2">
                        <input type="text" name="search" placeholder="Pesquisar por nome..." class="w-full px-4 py-2 border rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <select name="tipo" class="px-4 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="">Todos</option>
                            <option value="SAC" <?php if (isset($_GET['tipo']) && $_GET['tipo'] == 'SAC') echo 'selected'; ?>>SAC</option>
                            <option value="JARI" <?php if (isset($_GET['tipo']) && $_GET['tipo'] == 'JARI') echo 'selected'; ?>>JARI</option>
                            <option value="PCD" <?php if (isset($_GET['tipo']) && $_GET['tipo'] == 'PCD') echo 'selected'; ?>>PCD</option>
                            <option value="DAT" <?php if (isset($_GET['tipo']) && $_GET['tipo'] == 'DAT') echo 'selected'; ?>>DAT</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700 focus:outline-none">Pesquisar</button>
                    </form>
                </div>


                <!-- Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($submissoes_pagina as $item): ?>
                        <?php $style = getFormularioStyle($item['tipo']); ?>
                        <div class="form-card <?php echo $style['bg']; ?> <?php echo $style['border']; ?> <?php echo $style['text']; ?> border-l-4 p-6 rounded-lg shadow-md" data-tipo="<?php echo $item['tipo']; ?>">
                            <div class="flex items-center mb-4">
                                <span class="material-icons text-4xl"><?php echo $style['icon']; ?></span>
                                <h3 class="ml-4 text-xl font-bold"><?php echo $item['tipo']; ?> - ID: <?php echo $item['id']; ?></h3>
                            </div>
                            <p><strong>Nome:</strong> <?php echo htmlspecialchars($item['nome']); ?></p>
                            <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($item['data_submissao'])); ?></p>
                            <a href="detalhes_formulario.php?id=<?php echo $item['id']; ?>&tipo=<?php echo $item['tipo']; ?>" class="mt-4 inline-block text-blue-600 hover:underline">Ver Detalhes</a>
                        </div>
                    <?php endforeach; ?>
                </div>

                
                <!-- Paginação -->
                <div class="flex justify-center mt-6 space-x-2">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?php echo $pagina - 1; ?>&search=<?php echo urlencode($search); ?>&tipo=<?php echo urlencode($tipo_filter); ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Anterior</a>
                    <?php endif; ?>
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded">Página <?php echo $pagina; ?> de <?php echo $total_pages; ?></span>
                    <?php if ($pagina < $total_pages): ?>
                        <a href="?pagina=<?php echo $pagina + 1; ?>&search=<?php echo urlencode($search); ?>&tipo=<?php echo urlencode($tipo_filter); ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Próxima</a>
                    <?php endif; ?>
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
