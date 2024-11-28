<?php
session_start();
include '../env/config.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$erro = '';
$email_existente = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $senha_confirmacao = $_POST['senha_confirmacao'];

    // Verifica se o e-mail já existe na tabela usuarios_pendentes ou usuarios
    $sql_check = "SELECT email FROM usuarios_pendentes WHERE email = ? UNION SELECT email FROM usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('ss', $email, $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $email_existente = true;
    }

    if ($email_existente) {
        $erro = 'O e-mail fornecido já está em uso. Por favor, escolha outro e-mail.';
    } elseif ($senha !== $senha_confirmacao) {
        $erro = 'As senhas não coincidem.';
    } else {
        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

        // Insere o usuário na tabela de usuários pendentes
        $sql = "INSERT INTO usuarios_pendentes (nome, email, senha) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $nome, $email, $senha_hash);

        if ($stmt->execute()) {
            // Exibe o modal de confirmação
            echo "<script>
                    window.onload = function() {
                        document.getElementById('confirmationModal').classList.remove('hidden');
                    }
                  </script>";
        } else {
            $erro = 'Erro ao registrar. Tente novamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" x-data="{ showPassword: false }">

<head>
    <meta charset="UTF-8">
    <title>DEMUTRAN - Registro Administrativo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./assets/bk.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    [x-cloak] {
        display: none;
    }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <img src="./assets/logo-demutran.png" alt="DEMUTRAN Logo" class="mx-auto h-20 mb-3">
            <h2 class="text-2xl font-bold text-white">Solicite Seu Acesso</h2>
            <p class="text-gray-200 text-sm">Preencha os dados para cadastro</p>
        </div>

        <!-- Card do formulário -->
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-2xl p-8">
            <?php if ($erro): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <p class="text-sm text-red-700"><?php echo $erro; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <form action="registro.php" method="POST" class="space-y-6">
                <!-- Campo Nome -->
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="nome" id="nome" required
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Digite seu nome completo">
                    </div>
                </div>

                <!-- Campo Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" required
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="seu@email.com">
                    </div>
                </div>

                <!-- Campo Senha -->
                <div x-data="{ show: false }">
                    <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" name="senha" id="senha" required
                            class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" @click="show = !show" class="text-gray-400 hover:text-gray-600">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Campo Confirmar Senha -->
                <div x-data="{ show: false }">
                    <label for="senha_confirmacao" class="block text-sm font-medium text-gray-700 mb-1">Confirme a
                        Senha</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" name="senha_confirmacao" id="senha_confirmacao"
                            required
                            class="block w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" @click="show = !show" class="text-gray-400 hover:text-gray-600">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Botão de Registro -->
                <button type="submit"
                    class="w-full flex justify-center items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg transition duration-300 font-medium">
                    <i class="fas fa-user-plus"></i>
                    Solicitar Registro
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Já tem uma conta?
                    <a href="login.php" class="text-blue-600 hover:text-blue-700 font-medium">
                        Faça login
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-200">© <?php echo date('Y'); ?> DEMUTRAN. Todos os direitos reservados.</p>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div id="confirmationModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-2xl p-8 w-full max-w-md mx-4">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Cadastro Pendente de Aprovação</h2>
            <p class="text-gray-600">Seu cadastro foi recebido com sucesso. Após aprovação, você receberá uma
                notificação por e-mail.</p>
            <div class="mt-6 flex justify-end">
                <button onclick="closeModal()"
                    class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-check"></i>
                    Entendi
                </button>
            </div>
        </div>
    </div>

    <script>
    function closeModal() {
        document.getElementById('confirmationModal').classList.add('hidden');
        window.location.href = 'login.php';
    }
    </script>

</body>

</html>