<?php
session_start();
include 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Função para contar notificações não lidas
function contarNotificacoesNaoLidas($conn) {
    $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE lida = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

// Obter usuários pendentes
$sql = "SELECT * FROM usuarios_pendentes ORDER BY data_registro DESC";
$result = $conn->query($sql);
$usuariosPendentes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $usuariosPendentes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }" x-init="$refs.loading.classList.add('hidden')">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários Pendentes</title>
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
                    <a href="gerenciar_noticias.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">article</span>
                        <span class="ml-3">Notícias</span>
                    </a>
                    <a href="usuarios.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                        <span class="material-icons">people</span>
                        <span class="ml-3 font-semibold">Usuários</span>
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
                        <a href="gerenciar_noticias.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">article</span>
                            <span class="ml-3">Notícias</span>
                        </a>
                        <a href="usuarios.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                            <span class="material-icons">people</span>
                            <span class="ml-3 font-semibold">Usuários</span>
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
                    <h2 class="text-xl font-semibold text-gray-800">Gerenciar Usuários Pendentes</h2>
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
                <?php if (count($usuariosPendentes) > 0): ?>
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-700 mb-6">Usuários Pendentes</h2>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data de Registro</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($usuariosPendentes as $usuario): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d/m/Y', strtotime($usuario['data_registro'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button onclick="aceitarUsuario(<?php echo $usuario['id']; ?>)" class="text-green-600 hover:text-green-900 mr-4">Aceitar</button>
                                            <button onclick="recusarUsuario(<?php echo $usuario['id']; ?>)" class="text-red-600 hover:text-red-900">Recusar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-700 mb-6">Usuários Pendentes</h2>
                        <p class="text-gray-600">Não há usuários pendentes no momento.</p>
                    </div>
                <?php endif; ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">&copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os direitos reservados.</p>
            </footer>
        </div>
    </div>

    <!-- Modais -->
    <!-- Modal de Confirmação de Aceite -->
    <div id="modal-aceitar" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirmar Aceitação</h2>
            <p>Tem certeza que deseja aceitar este usuário?</p>
            <div class="mt-6 flex justify-end space-x-4">
                <button id="cancelar-aceitar" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
                <button id="confirmar-aceitar" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Aceitar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Recusa -->
    <div id="modal-recusar" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirmar Recusa</h2>
            <p>Tem certeza que deseja recusar este usuário?</p>
            <div class="mt-6 flex justify-end space-x-4">
                <button id="cancelar-recusar" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
                <button id="confirmar-recusar" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Recusar</button>
            </div>
        </div>
    </div>

    <script>
        let usuarioIdParaAceitar = null;
        let usuarioIdParaRecusar = null;

        // Função para aceitar usuário
function aceitarUsuario(id) {
    usuarioIdParaAceitar = id;
    document.getElementById('modal-aceitar').classList.remove('hidden');
}

document.getElementById('confirmar-aceitar').addEventListener('click', function() {
    if (usuarioIdParaAceitar) {
        fetch('./aceitar_usuario_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: usuarioIdParaAceitar })
        })
        .then(response => response.text().then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erro ao aceitar usuário: ' + data.message);
                }
            } catch (error) {
                console.error('Erro ao analisar JSON:', error);
                console.error('Resposta do servidor:', text);
                alert('Erro ao processar a resposta do servidor.');
            }
        }))
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro na requisição: ' + error);
        });
    }
});


function recusarUsuario(id) {
    usuarioIdParaRecusar = id;
    document.getElementById('modal-recusar').classList.remove('hidden');
}

document.getElementById('confirmar-recusar').addEventListener('click', function() {
    if (usuarioIdParaRecusar) {
        fetch('./recusar_usuario_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: usuarioIdParaRecusar })
        })
        .then(response => response.text().then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erro ao recusar usuário: ' + data.message);
                }
            } catch (error) {
                console.error('Erro ao analisar JSON:', error);
                console.error('Resposta do servidor:', text);
                alert('Erro ao processar a resposta do servidor.');
            }
        }))
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro na requisição: ' + error);
        });
    }
});

        // Cancelar Aceitação
        document.getElementById('cancelar-aceitar').addEventListener('click', function() {
            document.getElementById('modal-aceitar').classList.add('hidden');
        });

        // Confirmar Aceitação


        // Cancelar Recusa
        document.getElementById('cancelar-recusar').addEventListener('click', function() {
            document.getElementById('modal-recusar').classList.add('hidden');
        });

        // Confirmar Recusa

    </script>
</body>
</html>
