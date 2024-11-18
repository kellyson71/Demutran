<?php
session_start();
include 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obtém todas as notícias
$sql = "SELECT * FROM noticias ORDER BY data_publicacao DESC";
$result = $conn->query($sql);
$noticias = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $noticias[] = $row;
    }
}

// Função para contar notificações não lidas
function contarNotificacoesNaoLidas($conn) {
    $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE lida = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }" x-init="$refs.loading.classList.add('hidden')">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Notícias</title>
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
        .noticia-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body class="bg-gray-100 font-roboto">
    <!-- Loader -->
    <div x-ref="loading" class="fixed inset-0 bg-white z-50 flex items-center justify-center hidden">
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
                    <a href="formularios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">assignment</span>
                        <span class="ml-3">Formulários</span>
                    </a>
                    <a href="gerenciar_noticias.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                        <span class="material-icons">article</span>
                        <span class="ml-3 font-semibold">Notícias</span>
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
                        <a href="formularios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">assignment</span>
                            <span class="ml-3">Formulários</span>
                        </a>
                        <a href="gerenciar_noticias.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                            <span class="material-icons">article</span>
                            <span class="ml-3 font-semibold">Notícias</span>
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
                    <h2 class="text-xl font-semibold text-gray-800">Gerenciar Notícias</h2>
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
                        <!-- Dropdown de Notificações -->
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50">
                            <div class="p-4 border-b text-gray-700 font-bold">Notificações</div>
                            <ul>
                                <!-- Adicione suas notificações aqui -->
                                <li class="p-4 border-b hover:bg-gray-50">
                                    <a href="#" class="block">
                                        <p class="font-medium text-gray-800">Nova mensagem</p>
                                        <p class="text-sm text-gray-600">Você tem uma nova mensagem.</p>
                                    </a>
                                </li>
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
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-700">Lista de Notícias</h2>
                    <a href="adicionar_noticia.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition-all">
                        + Adicionar Notícia
                    </a>
                </div>

                <!-- Exibição de Notícias em Cartões -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($noticias as $noticia): ?>
                        <div class="bg-white shadow-lg rounded-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300">
                            <?php if (!empty($noticia['imagem_url'])): ?>
                                <img src="<?php echo $noticia['imagem_url']; ?>" alt="Imagem da Notícia" class="noticia-img">
                            <?php else: ?>
                                <img src="placeholder.png" alt="Imagem da Notícia" class="noticia-img">
                            <?php endif; ?>
                            <div class="p-4 flex flex-col h-full">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo $noticia['titulo']; ?></h3>
                                <p class="text-sm text-gray-600 flex-grow"><?php echo $noticia['resumo']; ?></p>
                                <p class="text-xs text-gray-500 mt-4">Publicado em: <?php echo date('d/m/Y', strtotime($noticia['data_publicacao'])); ?></p>
                                <div class="flex justify-end mt-4 space-x-4">
                                    <a href="editar_noticia.php?id=<?php echo $noticia['id']; ?>" class="text-blue-500 hover:text-blue-700">Editar</a>
                                    <button 
                                        class="text-red-500 hover:text-red-700" 
                                        onclick="confirmarExclusao(<?php echo $noticia['id']; ?>)">
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">&copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os direitos reservados.</p>
            </footer>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div id="modal-confirmacao" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirmar Exclusão</h2>
            <p>Tem certeza que deseja excluir esta notícia?</p>
            <div class="mt-6 flex justify-end space-x-4">
                <button id="cancelar" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
                <button id="confirmar" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Excluir</button>
            </div>
        </div>
    </div>

    <script>
        let noticiaIdParaExcluir = null;

        // Função para abrir o modal de confirmação
        function confirmarExclusao(id) {
            noticiaIdParaExcluir = id;
            document.getElementById('modal-confirmacao').classList.remove('hidden');
        }

        // Fechar modal ao clicar em "Cancelar"
        document.getElementById('cancelar').addEventListener('click', function() {
            document.getElementById('modal-confirmacao').classList.add('hidden');
        });

        // Função para realizar a exclusão via AJAX
        document.getElementById('confirmar').addEventListener('click', function() {
            if (noticiaIdParaExcluir) {
                fetch('./excluir_formulario_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: noticiaIdParaExcluir, tipo: 'noticias' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // alert('Notícia excluída com sucesso!');
                        window.location.reload(); // Recarregar a página após exclusão
                    } else {
                        alert('Erro ao excluir notícia: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erro na requisição: ' + error);
                });
            }
        });
    </script>
</body>
</html>
