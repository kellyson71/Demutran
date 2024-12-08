<?php
session_start();
include '../env/config.php';
include './includes/template.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Função para obter informações do usuário
function obterInformacoesUsuario($conn, $usuario_id) {
    $sql = "SELECT nome, email, data_registro FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function contarNotificacoesNaoLidas($conn) {
    $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE lida = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

function obterUltimosFormulariosSAC($conn) {
    $sql = "SELECT * FROM sac ORDER BY id DESC LIMIT 2";
    return $conn->query($sql);
}

$sacFormularios = obterUltimosFormulariosSAC($conn);
$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);
$usuario = obterInformacoesUsuario($conn, $_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }">

<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuário</title>
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

<body class="bg-gray-100 font-roboto min-h-screen flex flex-col">
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
                <?php echo getSidebarHtml('perfil'); ?>
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
                    <?php echo getSidebarHtml('perfil'); ?>
                </div>
            </aside>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php 
            $topbarHtml = getTopbarHtml('Perfil', $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="max-w-4xl mx-auto">
                    <!-- Profile Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 mb-6 shadow-lg">
                        <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
                            <div class="relative group">
                                <form id="avatarForm" class="m-0">
                                    <?php
                                    $avatarUrl = $_SESSION['usuario_avatar'] ?? '';
                                    $nome = $_SESSION['usuario_nome'];
                                    $iniciais = strtoupper(mb_substr($nome, 0, 1) . mb_substr(strstr($nome, ' '), 1, 1));
                                    
                                    if ($avatarUrl) {
                                        echo "<img id='avatarPreview' src='{$avatarUrl}?v=" . time() . "' 
                                            alt='Avatar' class='w-32 h-32 rounded-full object-cover ring-4 ring-white/50 transition duration-300 group-hover:ring-white'
                                            onerror=\"this.onerror=null; this.parentNode.innerHTML='<div id=\\\'avatarPreview\\\' class=\\\'w-32 h-32 rounded-full bg-white/20 flex items-center justify-center text-white text-4xl font-bold ring-4 ring-white/50\\\'>{$iniciais}</div>';\">";
                                    } else {
                                        echo "<div id='avatarPreview' class='w-32 h-32 rounded-full bg-white/20 flex items-center justify-center text-white text-4xl font-bold ring-4 ring-white/50'>
                                                {$iniciais}
                                            </div>";
                                    }
                                    ?>
                                    <label for="avatar"
                                        class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                                        <span class="material-icons text-white">photo_camera</span>
                                    </label>
                                    <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden">
                                </form>
                            </div>
                            <div class="text-center md:text-left">
                                <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
                                    <?php echo $usuario['nome']; ?></h1>
                                <p class="text-blue-100">Administrador do Sistema</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ 
                            editing: false, 
                            changingPassword: false,
                            nome: '<?php echo addslashes($usuario['nome']); ?>', 
                            email: '<?php echo addslashes($usuario['email']); ?>',
                            senha_atual: '',
                            nova_senha: '',
                            confirmar_senha: '',
                            
                            // Add methods inside x-data
                            submitPasswordChange() {
                                if (!this.senha_atual || !this.nova_senha || !this.confirmar_senha) {
                                    showMessage('Por favor, preencha todos os campos.', 'error');
                                    return;
                                }

                                if (this.nova_senha !== this.confirmar_senha) {
                                    showMessage('As novas senhas não coincidem.', 'error');
                                    return;
                                }

                                if (this.nova_senha.length < 8) {
                                    showMessage('A nova senha deve ter pelo menos 8 caracteres.', 'error');
                                    return;
                                }

                                fetch('atualizar_senha.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        senha_atual: this.senha_atual,
                                        nova_senha: this.nova_senha
                                    })
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.success) {
                                        this.changingPassword = false;
                                        this.senha_atual = '';
                                        this.nova_senha = '';
                                        this.confirmar_senha = '';
                                        showMessage('Senha alterada com sucesso!', 'success');
                                    } else {
                                        throw new Error(result.message);
                                    }
                                })
                                .catch(error => {
                                    showMessage(error.message, 'error');
                                });
                            },

                            submitForm() {
                                const data = {
                                    nome: this.nome,
                                    email: this.email
                                };

                                fetch('atualizar_perfil.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify(data)
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.success) {
                                        this.editing = false;
                                        showMessage('Perfil atualizado com sucesso!', 'success');
                                        // Update session data
                                        document.querySelectorAll('.user-name').forEach(el => {
                                            el.textContent = data.nome;
                                        });
                                    } else {
                                        throw new Error(result.message || 'Erro ao atualizar perfil');
                                    }
                                })
                                .catch(error => {
                                    showMessage(error.message, 'error');
                                });
                            }
                        }">

                        <!-- Informações do Perfil -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <span class="material-icons mr-2">person</span>
                                    Informações Pessoais
                                </h2>
                            </div>

                            <!-- View Mode -->
                            <div x-show="!editing" class="space-y-4 transition-all duration-300">
                                <div class="flex items-center space-x-2 p-3 rounded-lg bg-gray-50">
                                    <span class="material-icons text-blue-500">person</span>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500">Nome</span>
                                        <span class="text-gray-700 font-medium" x-text="nome"></span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 p-3 rounded-lg bg-gray-50">
                                    <span class="material-icons text-blue-500">email</span>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500">Email</span>
                                        <span class="text-gray-700 font-medium" x-text="email"></span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 p-3 rounded-lg bg-gray-50">
                                    <span class="material-icons text-blue-500">calendar_today</span>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500">Data de Registro</span>
                                        <span class="text-gray-700 font-medium">
                                            <?php echo date('d/m/Y', strtotime($usuario['data_registro'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Mode -->
                            <div x-show="editing" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100" class="space-y-4">
                                <div class="relative">
                                    <label class="text-sm text-gray-600 mb-1 block">Nome</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <span class="material-icons text-gray-400">person</span>
                                        </span>
                                        <input type="text" x-model="nome"
                                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Seu nome">
                                    </div>
                                </div>
                                <div class="relative">
                                    <label class="text-sm text-gray-600 mb-1 block">Email</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <span class="material-icons text-gray-400">email</span>
                                        </span>
                                        <input type="email" x-model="email"
                                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Seu email">
                                    </div>
                                </div>
                                <div class="flex space-x-3 pt-4">
                                    <button @click="submitForm()"
                                        class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center justify-center">
                                        <span class="material-icons text-sm mr-2">save</span>
                                        Salvar Alterações
                                    </button>
                                    <button @click="editing = false"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center justify-center">
                                        <span class="material-icons text-sm mr-2">close</span>
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Ações Rápidas -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="material-icons mr-2">settings</span>
                                Ações Rápidas
                            </h2>

                            <!-- Password Change Form -->
                            <div x-show="changingPassword" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100" class="space-y-4">
                                <div class="relative">
                                    <label class="text-sm text-gray-600 mb-1 block">Senha Atual</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <span class="material-icons text-gray-400">lock</span>
                                        </span>
                                        <input type="password" x-model="senha_atual"
                                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Digite sua senha atual">
                                    </div>
                                </div>
                                <div class="relative">
                                    <label class="text-sm text-gray-600 mb-1 block">Nova Senha</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <span class="material-icons text-gray-400">vpn_key</span>
                                        </span>
                                        <input type="password" x-model="nova_senha"
                                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Digite a nova senha">
                                    </div>
                                </div>
                                <div class="relative">
                                    <label class="text-sm text-gray-600 mb-1 block">Confirmar Nova Senha</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <span class="material-icons text-gray-400">check_circle</span>
                                        </span>
                                        <input type="password" x-model="confirmar_senha"
                                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Confirme a nova senha">
                                    </div>
                                </div>
                                <div class="flex space-x-3 pt-4">
                                    <button @click="submitPasswordChange()"
                                        class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200 flex items-center justify-center">
                                        <span class="material-icons text-sm mr-2">save</span>
                                        Alterar Senha
                                    </button>
                                    <button
                                        @click="changingPassword = false; senha_atual = ''; nova_senha = ''; confirmar_senha = '';"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center justify-center">
                                        <span class="material-icons text-sm mr-2">close</span>
                                        Cancelar
                                    </button>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div x-show="!changingPassword && !editing" class="grid grid-cols-1 gap-4">
                                <button @click="editing = true"
                                    class="w-full p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300 flex items-center justify-center group">
                                    <span
                                        class="material-icons mr-2 group-hover:transform group-hover:scale-110 transition-transform">edit</span>
                                    Editar Perfil
                                </button>
                                <button @click="changingPassword = true"
                                    class="w-full p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition duration-300 flex items-center justify-center group">
                                    <span
                                        class="material-icons mr-2 group-hover:transform group-hover:scale-110 transition-transform">lock</span>
                                    Alterar Senha
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Status Message -->
                    <div id="statusMessage" class="mt-4 text-center hidden"></div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">&copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os
                    direitos reservados.</p>
            </footer>
        </div>
    </div>

    <script>
    const form = document.getElementById('avatarForm');
    const fileInput = document.getElementById('avatar');
    const preview = document.getElementById('avatarPreview');
    const status = document.getElementById('statusMessage');

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className =
                    'w-32 h-32 rounded-full mb-4 object-cover ring-4 ring-blue-500 ring-offset-4';
                img.id = 'avatarPreview';

                const oldPreview = document.getElementById('avatarPreview');
                oldPreview.parentNode.replaceChild(img, oldPreview);

                // Auto submit when file is selected
                form.dispatchEvent(new Event('submit'));
            }
            reader.readAsDataURL(file);
        }
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!fileInput.files || !fileInput.files[0]) {
            status.classList.remove('hidden');
            status.classList.add('text-red-500');
            status.textContent = 'Por favor, selecione uma imagem.';
            return;
        }

        const formData = new FormData();
        formData.append('avatar', fileInput.files[0]);

        status.classList.remove('hidden');
        status.className = 'mt-2 text-sm text-blue-500';
        status.textContent = 'Enviando...';

        try {
            const response = await fetch('upload_avatar.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            status.classList.remove('text-blue-500', 'text-red-500', 'text-green-500');
            status.classList.add(result.success ? 'text-green-500' : 'text-red-500');
            status.textContent = result.message;

            if (result.success) {
                setTimeout(() => {
                    status.classList.add('hidden');
                    // Refresh only the avatar images on the page
                    document.querySelectorAll('img[src*="avatar"]').forEach(img => {
                        img.src = result.avatarUrl + '?v=' + new Date().getTime();
                    });
                }, 1500);
            }
        } catch (error) {
            console.error('Erro:', error);
            status.classList.remove('text-blue-500');
            status.classList.add('text-red-500');
            status.textContent = 'Erro ao fazer upload.';
        }
    });

    // Add the showMessage function as a global function
    function showMessage(message, type) {
        const status = document.getElementById('statusMessage');
        status.textContent = message;
        status.className = `mt-4 text-center p-3 rounded-lg animate-fade-in-down ${
            type === 'success' ? 'text-green-500 bg-green-50' : 'text-red-500 bg-red-50'
        }`;
        status.classList.remove('hidden');

        setTimeout(() => {
            status.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => status.classList.add('hidden'), 300);
        }, 3000);
    }
    </script>
</body>

</html>