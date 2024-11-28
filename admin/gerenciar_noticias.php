<?php
session_start();
include '../env/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Modificar a query de busca das notícias para suportar filtros
$search = isset($_GET['search']) ? $_GET['search'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'recent';

$sql = "SELECT *, 
        CASE 
            WHEN data_publicacao > CURDATE() THEN 'agendada'
            ELSE 'publicada'
        END as status_publicacao 
        FROM noticias WHERE 1=1";

// Adicionar filtro de busca
if (!empty($search)) {
    $search = "%{$search}%";
    $sql .= " AND (titulo LIKE ? OR resumo LIKE ?)";
}

// Adicionar ordenação
$sql .= " ORDER BY data_publicacao " . ($order === 'oldest' ? 'ASC' : 'DESC');

$stmt = $conn->prepare($sql);

// Bind parameters se houver busca
if (!empty($search)) {
    $stmt->bind_param('ss', $search, $search);
}

$stmt->execute();
$result = $stmt->get_result();
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
    [x-cloak] {
        display: none;
    }

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
                            <span
                                class="absolute top-0 right-0 bg-red-600 text-white rounded-full px-1 text-xs"><?php echo $notificacoesNaoLidas; ?></span>
                            <?php endif; ?>
                        </button>
                        <!-- Dropdown de Notificações -->
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50">
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
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-50">
                            <div class="p-4 border-b text-gray-700 font-bold"><?php echo $_SESSION['usuario_nome']; ?>
                            </div>
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

            <main class="flex-1 overflow-y-auto p-8 bg-gradient-to-br from-gray-50 to-gray-100">
                <!-- Header Section -->
                <div class="max-w-7xl mx-auto mb-8">
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Notícias</h1>
                                <p class="mt-2 text-gray-600">Gerencie o conteúdo noticioso do portal de forma eficiente
                                </p>
                            </div>
                            <a href="adicionar_noticia.php"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-blue-500/25">
                                <span class="material-icons text-sm mr-2">add</span>
                                Nova Notícia
                            </a>
                        </div>

                        <!-- Search and Filters -->
                        <div class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-[300px]">
                                <div class="relative">
                                    <input type="text" id="searchInput"
                                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                        class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all duration-200"
                                        placeholder="Pesquisar notícias...">
                                    <span
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 material-icons">search</span>
                                </div>
                            </div>
                            <div class="flex gap-2 flex-wrap">
                                <button onclick="aplicarFiltro('recent')"
                                    class="inline-flex items-center px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 <?php echo ($order === 'recent' ? 'bg-blue-50 text-blue-600 border-2 border-blue-200' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'); ?>">
                                    <span class="material-icons text-sm mr-2">schedule</span>
                                    Mais recentes
                                </button>
                                <button onclick="aplicarFiltro('oldest')"
                                    class="inline-flex items-center px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 <?php echo ($order === 'oldest' ? 'bg-blue-50 text-blue-600 border-2 border-blue-200' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'); ?>">
                                    <span class="material-icons text-sm mr-2">history</span>
                                    Mais antigas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- News Grid -->
                <?php if (!empty($noticias)): ?>
                <div class="max-w-7xl mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($noticias as $noticia): ?>
                        <div
                            class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden">
                            <div class="relative aspect-video">
                                <?php if (!empty($noticia['imagem_url'])): ?>
                                <img src="<?php echo $noticia['imagem_url']; ?>"
                                    alt="<?php echo htmlspecialchars($noticia['titulo']); ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <?php else: ?>
                                <div
                                    class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <span class="material-icons text-4xl text-gray-400">image</span>
                                </div>
                                <?php endif; ?>

                                <!-- Status Badge -->
                                <div class="absolute top-4 left-4 flex gap-2">
                                    <?php if ($noticia['status_publicacao'] === 'agendada'): ?>
                                    <span
                                        class="px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-lg shadow-sm">
                                        Agendada
                                    </span>
                                    <?php else: ?>
                                    <span
                                        class="px-3 py-1.5 bg-green-500 text-white text-xs font-medium rounded-lg shadow-sm">
                                        Publicada
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons -->
                                <div
                                    class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="editar_noticia.php?id=<?php echo $noticia['id']; ?>"
                                        class="p-2 bg-white/90 backdrop-blur-sm rounded-lg hover:bg-blue-500 hover:text-white transition-colors duration-200">
                                        <span class="material-icons text-sm">edit</span>
                                    </a>
                                    <button onclick="confirmarExclusao(<?php echo $noticia['id']; ?>)"
                                        class="p-2 bg-white/90 backdrop-blur-sm rounded-lg hover:bg-red-500 hover:text-white transition-colors duration-200">
                                        <span class="material-icons text-sm">delete</span>
                                    </button>
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="mb-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 line-clamp-2 mb-2 group-hover:text-blue-600 transition-colors">
                                        <?php echo htmlspecialchars($noticia['titulo']); ?>
                                    </h3>
                                    <p class="text-gray-600 text-sm line-clamp-2">
                                        <?php echo htmlspecialchars($noticia['resumo']); ?>
                                    </p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($noticia['data_publicacao'])); ?>
                                    </span>
                                    <a href="visualizar_noticia.php?id=<?php echo $noticia['id']; ?>"
                                        class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium text-sm group/link">
                                        Ver mais
                                        <span
                                            class="material-icons text-sm ml-1 transition-transform group-hover/link:translate-x-1">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php else: ?>
                <!-- Empty State -->
                <div class="max-w-md mx-auto text-center py-12">
                    <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-icons text-4xl text-blue-500">article</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Nenhuma notícia encontrada</h3>
                    <p class="text-gray-600 mb-6">Comece adicionando sua primeira notícia ao portal</p>
                    <a href="adicionar_noticia.php"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-lg hover:shadow-blue-500/25">
                        <span class="material-icons text-sm mr-2">add</span>
                        Criar Nova Notícia
                    </a>
                </div>
                <?php endif; ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">&copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os
                    direitos reservados.</p>
            </footer>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div id="modal-confirmacao"
        class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirmar Exclusão</h2>
            <p>Tem certeza que deseja excluir esta notícia?</p>
            <div class="mt-6 flex justify-end space-x-4">
                <button id="cancelar"
                    class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
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
                    body: JSON.stringify({
                        id: noticiaIdParaExcluir,
                        tipo: 'noticias'
                    })
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

    // Adicionar antes do script existente
    let searchTimeout;

    // Função para aplicar filtros
    function aplicarFiltros(search = null, order = null) {
        const urlParams = new URLSearchParams(window.location.search);

        if (search !== null) {
            if (search === '') {
                urlParams.delete('search');
            } else {
                urlParams.set('search', search);
            }
        }

        if (order !== null) {
            if (order === 'recent') {
                urlParams.delete('order');
            } else {
                urlParams.set('order', order);
            }
        }

        const newUrl = `${window.location.pathname}${urlParams.toString() ? '?' + urlParams.toString() : ''}`;
        window.location.href = newUrl;
    }

    // Função para o botão de filtro
    function aplicarFiltro(order) {
        aplicarFiltros(null, order);
    }

    // Listener para o campo de busca com debounce
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            aplicarFiltros(e.target.value);
        }, 500); // Aguarda 500ms após o usuário parar de digitar
    });
    </script>
</body>

</html>