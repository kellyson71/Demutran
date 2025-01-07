<?php
session_start();
include '../env/config.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);

    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['is_admin'] = $usuario['is_admin'];
            header('Location: index.php');
            exit();
        } else {
            $erro = 'Usuário não encontrado.';
        }
    } else {
        $erro = 'Erro na execução da consulta.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" x-data="{ showPassword: false }">

<head>
    <meta charset="UTF-8">
    <title>DEMUTRAN - Portal Administrativo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./assets/bk.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <img src="./assets/logo-demutran.png" alt="DEMUTRAN Logo" class="mx-auto h-20 mb-3">
            <h2 class="text-2xl font-bold text-white">Portal Administrativo</h2>
            <p class="text-gray-200 text-sm">Acesse sua conta para continuar</p>
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

            <form action="login.php" method="POST" class="space-y-6">
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

                <!-- Botão de Login -->
                <button type="submit"
                    class="w-full flex justify-center items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg transition duration-300 font-medium">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-200">© <?php echo date('Y'); ?> DEMUTRAN. Todos os direitos reservados.</p>
        </div>
    </div>
</body>

</html>