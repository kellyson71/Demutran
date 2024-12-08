<?php
session_start();
include '../env/config.php';
include './includes/template.php';

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

// Configuração da paginação
$itens_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Modificar a query para incluir LIMIT e OFFSET
$sql_todos = "SELECT id, nome, email, data_registro FROM usuarios ORDER BY nome ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql_todos);
$stmt->bind_param("ii", $itens_por_pagina, $offset);
$stmt->execute();
$result_todos = $stmt->get_result();

// Contar total de registros
$sql_total = "SELECT COUNT(*) as total FROM usuarios";
$result_total = $conn->query($sql_total);
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $itens_por_pagina);

$todosUsuarios = [];
if ($result_todos->num_rows > 0) {
    while ($row = $result_todos->fetch_assoc()) {
        // Formatar os dados para o JavaScript
        $todosUsuarios[] = [
            'id' => $row['id'],
            'nome' => htmlspecialchars($row['nome']),
            'email' => htmlspecialchars($row['email']),
            'data_registro' => date('d/m/Y', strtotime($row['data_registro'])),
            'status' => 'ativo' // Valor padrão para usuários já aceitos
        ];
    }
}

// Converter para JSON com opções de formatação adequadas
$todosUsuariosJson = json_encode($todosUsuarios, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);
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
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userData', () => ({
            activeTab: 'pending',
            searchTerm: '',
            todosUsuarios: <?php echo $todosUsuariosJson; ?>,
            filteredUsers() {
                if (!this.searchTerm) return this.todosUsuarios;
                const searchTerm = this.searchTerm.toLowerCase();
                return this.todosUsuarios.filter(user =>
                    user.nome.toLowerCase().includes(searchTerm) ||
                    user.email.toLowerCase().includes(searchTerm)
                );
            },
            editarUsuario(usuario) {
                document.getElementById('editar-id').value = usuario.id;
                document.getElementById('editar-nome').value = usuario.nome;
                document.getElementById('editar-email').value = usuario.email;
                document.getElementById('modal-editar').classList.remove('hidden');
            }
        }))
    })
    </script>

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
                <?php echo getSidebarHtml('usuarios'); ?>
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
                    <?php echo getSidebarHtml('usuarios'); ?>
                </div>
            </aside>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php 
            $topbarHtml = getTopbarHtml('Gerenciar Usuários', $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Header Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-50">
                                <span class="material-icons text-blue-600">person_add</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Usuários Pendentes</h3>
                                <p class="text-2xl font-semibold text-gray-900"><?php echo count($usuariosPendentes); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-50">
                                <span class="material-icons text-green-600">groups</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Total de Usuários</h3>
                                <p class="text-2xl font-semibold text-gray-900"><?php echo count($todosUsuarios); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-50">
                                <span class="material-icons text-purple-600">verified_user</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Usuários Ativos</h3>
                                <p class="text-2xl font-semibold text-gray-900"><?php echo count($todosUsuarios); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mb-6" x-data="userData">
                    <div class="bg-white rounded-xl shadow-sm">
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                                <button @click="activeTab = 'pending'"
                                    :class="{ 'border-blue-500 text-blue-600': activeTab === 'pending' }"
                                    class="border-b-2 py-4 px-1 text-sm font-medium hover:text-gray-700 hover:border-gray-300 whitespace-nowrap focus:outline-none transition-colors">
                                    <span class="inline-flex items-center">
                                        <span class="material-icons text-sm mr-2">assignment_ind</span>
                                        Pendentes
                                        <?php if (count($usuariosPendentes) > 0): ?>
                                        <span class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2.5 rounded-full text-xs">
                                            <?php echo count($usuariosPendentes); ?>
                                        </span>
                                        <?php endif; ?>
                                    </span>
                                </button>
                                <button @click="activeTab = 'all'"
                                    :class="{ 'border-blue-500 text-blue-600': activeTab === 'all' }"
                                    class="border-b-2 py-4 px-1 text-sm font-medium hover:text-gray-700 hover:border-gray-300 whitespace-nowrap focus:outline-none transition-colors">
                                    <span class="inline-flex items-center">
                                        <span class="material-icons text-sm mr-2">group</span>
                                        Todos os Usuários
                                    </span>
                                </button>
                            </nav>
                        </div>

                        <!-- Search and Filters -->
                        <div class="p-4 bg-gray-50 border-b">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="relative flex-1 w-full">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                        <span class="material-icons text-gray-400 text-sm">search</span>
                                    </span>
                                    <input type="text" x-model="searchTerm"
                                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        placeholder="Buscar usuários...">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">
                                        <span class="material-icons text-sm mr-2">filter_list</span>
                                        Filtros
                                    </button>
                                    <button
                                        class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">
                                        <span class="material-icons text-sm mr-2">download</span>
                                        Exportar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Table Contents -->
                        <div x-show="activeTab === 'pending'">
                            <?php if (count($usuariosPendentes) > 0): ?>
                            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-xl font-semibold text-gray-800">Usuários Pendentes</h2>
                                        <span
                                            class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded-full"><?php echo count($usuariosPendentes); ?>
                                            pendentes</span>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left">
                                                    <span
                                                        class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</span>
                                                </th>
                                                <th class="px-6 py-3 text-left">
                                                    <span
                                                        class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</span>
                                                </th>
                                                <th class="px-6 py-3 text-left">
                                                    <span
                                                        class="text-xs font-medium text-gray-500 uppercase tracking-wider">Data</span>
                                                </th>
                                                <th class="px-6 py-3 text-right">
                                                    <span
                                                        class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <?php foreach ($usuariosPendentes as $usuario): ?>
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <div class="h-10 w-10 flex-shrink-0">
                                                            <span
                                                                class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                                <span
                                                                    class="text-xl text-gray-600"><?php echo substr($usuario['nome'], 0, 1); ?></span>
                                                            </span>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                <?php echo htmlspecialchars($usuario['nome']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">
                                                        <?php echo htmlspecialchars($usuario['email']); ?></div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo date('d/m/Y', strtotime($usuario['data_registro'])); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button onclick="aceitarUsuario(<?php echo $usuario['id']; ?>)"
                                                        class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 text-sm font-medium rounded-md hover:bg-green-100 transition-colors mr-2">
                                                        <span class="material-icons text-sm mr-1">check_circle</span>
                                                        Aceitar
                                                    </button>
                                                    <button onclick="recusarUsuario(<?php echo $usuario['id']; ?>)"
                                                        class="inline-flex items-center px-3 py-1 bg-red-50 text-red-700 text-sm font-medium rounded-md hover:bg-red-100 transition-colors">
                                                        <span class="material-icons text-sm mr-1">cancel</span>
                                                        Recusar
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                                <div
                                    class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                    <span class="material-icons text-gray-400 text-2xl">people</span>
                                </div>
                                <h2 class="text-xl font-semibold text-gray-800 mb-2">Nenhum Usuário Pendente</h2>
                                <p class="text-gray-500">Não há usuários aguardando aprovação no momento.</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div x-show="activeTab === 'all'" x-cloak>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left">
                                                <span
                                                    class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</span>
                                            </th>
                                            <th class="px-6 py-3 text-left">
                                                <span
                                                    class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</span>
                                            </th>
                                            <th class="px-6 py-3 text-left">
                                                <span
                                                    class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</span>
                                            </th>
                                            <th class="px-6 py-3 text-right">
                                                <span
                                                    class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="usuario in filteredUsers()" :key="usuario.id">
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <div class="h-10 w-10 flex-shrink-0">
                                                            <span
                                                                class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <span class="text-xl text-blue-600"
                                                                    x-text="usuario.nome.charAt(0)"></span>
                                                            </span>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900"
                                                                x-text="usuario.nome"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900" x-text="usuario.email"></div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                        :class="{
                                                            'bg-green-100 text-green-800': usuario.status === 'ativo',
                                                            'bg-yellow-100 text-yellow-800': usuario.status === 'pendente',
                                                            'bg-red-100 text-red-800': usuario.status === 'inativo'
                                                        }"
                                                        x-text="usuario.status ? usuario.status.charAt(0).toUpperCase() + usuario.status.slice(1) : 'Ativo'">
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button @click="editarUsuario(usuario)"
                                                        class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors">
                                                        <span class="material-icons text-sm mr-1">edit</span>
                                                        Editar
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow-sm">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Anterior
                        </button>
                        <button
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Próximo
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando <span class="font-medium">1</span> até <span class="font-medium">10</span> de
                                <span class="font-medium"><?php echo $total_registros; ?></span> resultados
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                aria-label="Pagination">
                                <button
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="material-icons text-sm">chevron_left</span>
                                </button>
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <a href="?pagina=<?php echo $i; ?>"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"><?php echo $i; ?></a>
                                <?php endfor; ?>
                                <button
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="material-icons text-sm">chevron_right</span>
                                </button>
                            </nav>
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

    <!-- Modais -->
    <!-- Modal de Confirmação de Aceite -->
    <div id="modal-aceitar" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirmar Aceitação</h2>
            <p>Tem certeza que deseja aceitar este usuário?</p>
            <div class="mt-6 flex justify-end space-x-4">
                <button id="cancelar-aceitar"
                    class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
                <button id="confirmar-aceitar"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Aceitar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Recusa -->
    <div id="modal-recusar" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirmar Recusa</h2>
            <p>Tem certeza que deseja recusar este usuário?</p>
            <div class="mt-6 flex justify-end space-x-4">
                <button id="cancelar-recusar"
                    class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
                <button id="confirmar-recusar"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Recusar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Usuário -->
    <div id="modal-editar" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Editar Usuário</h2>
            <form id="form-editar" class="space-y-4">
                <input type="hidden" id="editar-id">
                <div>
                    <label for="editar-nome" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input type="text" id="editar-nome"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="editar-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="editar-email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" id="cancelar-editar"
                        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Salvar
                    </button>
                </div>
            </form>
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
                    body: JSON.stringify({
                        id: usuarioIdParaAceitar
                    })
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
                    body: JSON.stringify({
                        id: usuarioIdParaRecusar
                    })
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

    // Editar Usuário
    document.getElementById('form-editar').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('editar-id').value;
        const nome = document.getElementById('editar-nome').value;
        const email = document.getElementById('editar-email').value;

        fetch('./editar_usuario_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id,
                    nome,
                    email
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erro ao editar usuário: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a requisição');
            });
    });

    document.getElementById('cancelar-editar').addEventListener('click', function() {
        document.getElementById('modal-editar').classList.add('hidden');
    });
    </script>
</body>

</html>