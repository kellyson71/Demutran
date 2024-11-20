<?php
session_start();
include '../env/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Função para obter informações do usuário
function obterInformacoesUsuario($conn, $usuario_id) {
    $sql = "SELECT nome, email FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Atualiza as informações do usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);

    if (empty($nome) || empty($email)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nome, $email, $_SESSION['usuario_id']);
        if ($stmt->execute()) {
            $sucesso = 'Informações atualizadas com sucesso.';
            $_SESSION['usuario_nome'] = $nome;
        } else {
            $erro = 'Erro ao atualizar informações.';
        }
    }
}

$usuario = obterInformacoesUsuario($conn, $_SESSION['usuario_id']);

function contarNotificacoesNaoLidas($conn) {
    $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE lida = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }">

<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
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
            <main class="flex-1 overflow-y-auto p-6 flex flex-col items-center">
                <!-- Formulário de Edição de Perfil -->
                <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Perfil</h2>
                    <?php if (isset($erro)): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
                        <?php echo $erro; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($sucesso)): ?>
                    <div class="bg-green-100 text-green-700 p-4 rounded mb-6">
                        <?php echo $sucesso; ?>
                    </div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label for="nome" class="block text-gray-700 font-medium mb-2">Nome</label>
                            <input type="text" name="nome" id="nome"
                                class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
                                value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                        </div>
                        <div class="mb-6">
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" name="email" id="email"
                                class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
                                value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                        <div class="flex space-x-4">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Salvar
                                Alterações</button>
                            <a href="perfil.php"
                                class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400 transition">Cancelar</a>
                        </div>
                    </form>
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