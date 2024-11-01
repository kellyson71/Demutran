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

function obterUltimosFormularios($conn, $tabela, $limite = 5) {
    $sql = "SELECT * FROM $tabela ORDER BY id DESC LIMIT $limite";
    return $conn->query($sql);
}

$sacFormularios = obterUltimosFormularios($conn, 'sac');
$jariFormularios = obterUltimosFormularios($conn, 'solicitacoes_demutran');
$pcdFormularios = obterUltimosFormularios($conn, 'solicitacao_cartao');
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
        [x-cloak] { display: none; }
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
                <nav class="space-y-2 flex-1">
                    <a href="dashboard.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                        <span class="material-icons">dashboard</span>
                        <span class="ml-3 font-semibold">Dashboard</span>
                    </a>
                    <a href="formularios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">assignment</span>
                        <span class="ml-3">Formulários</span>
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
                        <a href="dashboard.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                            <span class="material-icons">dashboard</span>
                            <span class="ml-3 font-semibold">Dashboard</span>
                        </a>
                        <a href="formularios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">assignment</span>
                            <span class="ml-3">Formulários</span>
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
                    <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
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
                                $notificacoes = obterUltimosFormularios($conn, 'notificacoes');
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
                <!-- Welcome Banner -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?>!</h1>
                    <p class="text-gray-600 mt-2">Aqui está o resumo das atividades recentes.</p>
                </div>

                <!-- Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <span class="material-icons text-blue-600 text-4xl">assignment</span>
                            <div class="ml-4">
                                <p class="text-gray-600">Formulários SAC</p>
                                <p class="text-2xl font-bold"><?php echo $sacFormularios->num_rows; ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <span class="material-icons text-green-600 text-4xl">how_to_vote</span>
                            <div class="ml-4">
                                <p class="text-gray-600">Defesas JARI</p>
                                <p class="text-2xl font-bold"><?php echo $jariFormularios->num_rows; ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <span class="material-icons text-purple-600 text-4xl">accessible</span>
                            <div class="ml-4">
                                <p class="text-gray-600">Cartões PCD</p>
                                <p class="text-2xl font-bold"><?php echo $pcdFormularios->num_rows; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Submissions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Recent JARI Forms -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Últimas Defesas JARI</h2>
                        <?php while($formulario = $jariFormularios->fetch_assoc()): ?>
                            <div class="border-b py-2">
                                <a href="detalhes_formulario.php?id=<?php echo $formulario['id']; ?>&tipo=JARI" class="flex justify-between items-center">
                                    <div>
                                        <p class="text-gray-700 font-medium"><?php echo $formulario['nome']; ?></p>
                                        <p class="text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($formulario['data_submissao'])); ?></p>
                                    </div>
                                    <span class="material-icons text-gray-400">chevron_right</span>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Recent PCD Forms -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Últimos Formulários PCD</h2>
                        <?php while($formulario = $pcdFormularios->fetch_assoc()): ?>
                            <div class="border-b py-2">
                                <a href="detalhes_formulario.php?id=<?php echo $formulario['id']; ?>&tipo=PCD" class="flex justify-between items-center">
                                    <div>
                                        <p class="text-gray-700 font-medium"><?php echo $formulario['solicitante']; ?></p>
                                        <p class="text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($formulario['data_submissao'])); ?></p>
                                    </div>
                                    <span class="material-icons text-gray-400">chevron_right</span>
                                </a>
                            </div>
                        <?php endwhile; ?>
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
