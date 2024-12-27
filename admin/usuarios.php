<?php
session_start();
include '../env/config.php';
include './includes/template.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificação de admin com mensagem de erro
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Salvar mensagem de erro na sessão
    $_SESSION['error_message'] = 'Acesso negado. Esta página é restrita a administradores.';
    header('Location: index.php');
    exit();
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

// Configuração da paginação
$itens_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Query para buscar TODOS os usuários primeiro
$sql_total = "SELECT COUNT(*) as total FROM usuarios";
$result_total = $conn->query($sql_total);
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $itens_por_pagina);

// Query para buscar usuários com paginação
$sql_todos = "SELECT id, nome, email, data_registro, is_admin FROM usuarios ORDER BY nome ASC";
$result_todos = $conn->query($sql_todos);

$todosUsuarios = [];
if ($result_todos->num_rows > 0) {
    while ($row = $result_todos->fetch_assoc()) {
        $todosUsuarios[] = [
            'id' => $row['id'],
            'nome' => htmlspecialchars($row['nome']),
            'email' => htmlspecialchars($row['email']),
            'data_registro' => date('d/m/Y', strtotime($row['data_registro'])),
            'status' => 'ativo',
            'is_admin' => $row['is_admin']
        ];
    }
}

// Remova o LIMIT e OFFSET da query principal para mostrar todos os usuários
$todosUsuariosJson = json_encode($todosUsuarios, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }" x-init="$refs.loading.classList.add('hidden')">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS -->
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <!-- Ou melhor ainda, use uma versão local do Tailwind -->

    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Google Fonts (Roboto) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
    document.addEventListener('alpine:init', () => {
        // Criar o store global primeiro
        Alpine.store('mainData', {
            usuarios: <?php echo $todosUsuariosJson; ?>
        });

        Alpine.data('mainData', () => ({
            searchTerm: '',
            get usuarios() {
                return Alpine.store('mainData').usuarios;
            },
            get filteredUsers() {
                if (!this.searchTerm) return this.usuarios;
                const term = this.searchTerm.toLowerCase();
                return this.usuarios.filter(user => 
                    user.nome.toLowerCase().includes(term) || 
                    user.email.toLowerCase().includes(term)
                );
            },
            editarUsuario(usuario) {
                document.getElementById('editar-id').value = usuario.id;
                document.getElementById('editar-nome').value = usuario.nome;
                document.getElementById('editar-email').value = usuario.email;
                document.getElementById('modal-editar').classList.remove('hidden');
            }
        }));
    });
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
                <!-- Botão Criar Usuário -->
                <div class="mb-6 flex justify-end">
                    <button onclick="document.getElementById('modal-criar').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <span class="material-icons text-sm mr-2">person_add</span>
                        Criar Usuário
                    </button>
                </div>

                <!-- Header Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-50">
                                <span class="material-icons text-blue-600">groups</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Total de Usuários</h3>
                                <p class="text-2xl font-semibold text-gray-900"><?php echo count($todosUsuarios); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-50">
                                <span class="material-icons text-green-600">verified_user</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500">Usuários Ativos</h3>
                                <p class="text-2xl font-semibold text-gray-900"><?php echo count($todosUsuarios); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Usuários -->
                <div x-data="mainData" class="bg-white rounded-xl shadow-sm">
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
                                    <span class="material-icons text-sm mr-2">download</span>
                                    Exportar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Usuários -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</span>
                                    </th>
                                    <th class="px-6 py-3 text-left">
                                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</span>
                                    </th>
                                    <th class="px-6 py-3 text-left">
                                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Data de Registro</span>
                                    </th>
                                    <th class="px-6 py-3 text-left">
                                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</span>
                                    </th>
                                    <th class="px-6 py-3 text-right">
                                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="usuario in filteredUsers" :key="usuario.id">
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
                                            <div class="text-sm text-gray-500" x-text="usuario.data_registro"></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span x-show="usuario.is_admin == 1" 
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <span class="material-icons text-sm mr-1">admin_panel_settings</span>
                                                Administrador
                                            </span>
                                            <span x-show="usuario.is_admin == 0" 
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <span class="material-icons text-sm mr-1">person</span>
                                                Usuário
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                                <button @click="open = !open" type="button" class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors">
                                                    <span class="material-icons text-sm mr-1">more_vert</span>
                                                    Ações
                                                </button>

                                                <div x-show="open" 
                                                     @click.away="open = false"
                                                     class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
                                                     x-cloak>
                                                    <div class="py-1">
                                                        <!-- Editar -->
                                                        <button @click="editarUsuario(usuario); open = false"
                                                            class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            <span class="material-icons text-sm mr-3">edit</span>
                                                            Editar Usuário
                                                        </button>

                                                        <!-- Tornar Admin (apenas para não-admin) -->
                                                        <template x-if="usuario.is_admin == 0">
                                                            <button @click="tornarAdmin(usuario.id); open = false"
                                                                class="flex w-full items-center px-4 py-2 text-sm text-purple-700 hover:bg-purple-50">
                                                                <span class="material-icons text-sm mr-3">admin_panel_settings</span>
                                                                Tornar Administrador
                                                            </button>
                                                        </template>

                                                        <!-- Deletar -->
                                                        <button @click="deletarUsuario(usuario.id); open = false"
                                                            class="flex w-full items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                            <span class="material-icons text-sm mr-3">delete</span>
                                                            Apagar Usuário
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div x-data="mainData" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow-sm mt-4">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando 
                                <span class="font-medium" x-text="usuarios.length"></span> 
                                usuários
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="material-icons text-sm">chevron_left</span>
                                </button>
                                <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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

    <!-- Modal de Criação de Usuário -->
    <div id="modal-criar" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Criar Novo Usuário</h2>
        <form id="form-criar" class="space-y-4">
            <!-- Avatar Upload -->
            <div class="flex justify-center mb-6">
                <div class="relative group">
                    <div id="avatar-preview" class="w-32 h-32 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-4xl">
                        <span class="material-icons">person</span>
                    </div>
                    <label for="criar-avatar" class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                        <span class="material-icons text-white">photo_camera</span>
                    </label>
                    <input type="file" id="criar-avatar" name="avatar" accept="image/*" class="hidden">
                </div>
            </div>

            <div>
                <label for="criar-nome" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                <input type="text" id="criar-nome" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="criar-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="criar-email" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="criar-senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                <input type="password" id="criar-senha" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="criar-confirmar-senha" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
                <input type="password" id="criar-confirmar-senha" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus-border-blue-500">
            </div>
            <div class="flex items-center space-x-2 mt-4">
                <input type="checkbox" id="criar-admin" name="is_admin" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="criar-admin" class="text-sm text-gray-700">Usuário é administrador</label>
            </div>
            <div class="mt-6 flex justify-end space-x-4">
                <button type="button" onclick="document.getElementById('modal-criar').classList.add('hidden')"
                    class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                    Cancelar
                </button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Criar Usuário
                </button>
            </div>
        </form>
    </div>
</div>

    <script>
    // Remover código relacionado a aceitar/recusar
    // Manter apenas as funções de edição e criação

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

    // Adicionar ao JavaScript existente
    function showSuccessAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed top-4 right-4 z-50 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-lg';
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <span class="material-icons text-green-600 mr-2">check_circle</span>
                <div>
                    <p class="font-bold">Sucesso!</p>
                    <p>${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                    <span class="material-icons">close</span>
                </button>
            </div>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }

    // Modificar o event listener do form-criar para melhor tratamento de erros
    document.getElementById('form-criar').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Pegar o arquivo de avatar se existir
        const avatarInput = document.getElementById('criar-avatar');
        let avatarPromise = Promise.resolve(null);
        
        if (avatarInput.files[0]) {
            avatarPromise = new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.readAsDataURL(avatarInput.files[0]);
            });
        }

        // Quando o avatar estiver pronto (ou não existir), enviar o formulário
        avatarPromise.then(avatarBase64 => {
            const formData = {
                nome: document.getElementById('criar-nome').value,
                email: document.getElementById('criar-email').value,
                senha: document.getElementById('criar-senha').value,
                is_admin: document.getElementById('criar-admin').checked,
                avatar: avatarBase64
            };

            // Validar senha
            if (formData.senha !== document.getElementById('criar-confirmar-senha').value) {
                alert('As senhas não coincidem');
                return;
            }

            fetch('criar_usuario_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Adicionar novo usuário à lista sem recarregar a página
                    Alpine.store('mainData').usuarios.push({
                        id: data.usuario.id,
                        nome: data.usuario.nome,
                        email: data.usuario.email,
                        data_registro: data.usuario.data_registro,
                        status: 'ativo',
                        is_admin: data.usuario.is_admin,
                        avatar: data.usuario.avatar
                    });

                    // Limpar formulário
                    document.getElementById('form-criar').reset();
                    document.getElementById('avatar-preview').innerHTML = '<span class="material-icons">person</span>';
                    
                    // Mostrar mensagem de sucesso
                    showSuccessAlert('Usuário criado com sucesso!');
                    
                    // Fechar modal
                    document.getElementById('modal-criar').classList.add('hidden');
                } else {
                    alert(data.message || 'Erro ao criar usuário');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a requisição');
            });
        });
    });

    // Função para preview da imagem
    function setupAvatarPreview() {
        const fileInput = document.getElementById('criar-avatar');
        const preview = document.getElementById('avatar-preview');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full rounded-full object-cover">`;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Inicializar preview
    setupAvatarPreview();

    // Adicionar função para tornar usuário administrador
    function tornarAdmin(userId) {
        if (confirm('Tem certeza que deseja tornar este usuário um administrador?')) {
            fetch('tornar_admin_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessAlert('Usuário promovido a administrador com sucesso!');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('Erro ao promover usuário: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a requisição');
            });
        }
    }

    function deletarUsuario(userId) {
        if (confirm('Tem certeza que deseja apagar este usuário? Esta ação não pode ser desfeita.')) {
            fetch('deletar_usuario_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover usuário da lista no Alpine.js store
                    Alpine.store('mainData').usuarios = Alpine.store('mainData').usuarios.filter(u => u.id !== userId);
                    showSuccessAlert('Usuário apagado com sucesso!');
                } else {
                    alert('Erro ao apagar usuário: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a requisição');
            });
        }
    }
    </script>
</body>

</html>