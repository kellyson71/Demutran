<?php
session_start();
include 'config.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Verificar se o usuário está na tabela de usuários pendentes
    $sql_pendentes = "SELECT * FROM usuarios_pendentes WHERE email = ?";
    $stmt_pendentes = $conn->prepare($sql_pendentes);
    $stmt_pendentes->bind_param('s', $email);
    $stmt_pendentes->execute();
    $resultado_pendentes = $stmt_pendentes->get_result();

    if ($resultado_pendentes->num_rows === 1) {
        // Usuário pendente encontrado
        $erro = 'Seu cadastro ainda não foi aprovado. Você receberá uma notificação por e-mail quando for aprovado.';
    } else {
        // Verificar se o usuário está na tabela de usuários aprovados
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);

        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            if ($resultado->num_rows === 1) {
                $usuario = $resultado->fetch_assoc();
                if (password_verify($senha, $usuario['senha'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $erro = 'Senha incorreta.';
                }
            } else {
                $erro = 'Usuário não encontrado.';
            }
        } else {
            $erro = 'Erro na execução da consulta.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" x-data="{ showPassword: false }">
<head>
    <meta charset="UTF-8">
    <title>Login - Administração</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        [x-cloak] { display: none; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-lg overflow-hidden w-full max-w-4xl flex">
        <!-- Seção de Imagem -->
        <div class="hidden md:block md:w-1/2">
            <img src="./assets/demu.jpg" alt="Imagem Ilustrativa" class="w-full h-full object-cover">
        </div>

        <!-- Seção de Formulário -->
        <div class="w-full md:w-1/2 p-8">
            <h2 class="text-3xl font-semibold text-gray-800 text-center">Bem-vindo de volta!</h2>
            <p class="text-gray-600 text-center mb-6">Faça login para continuar</p>

            <?php if ($erro): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-4">
                <div>
                    <label for="email" class="block text-gray-700">E-mail</label>
                    <input type="email" name="email" id="email" class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>
                <div x-data="{ show: false }" class="relative">
                    <label for="senha" class="block text-gray-700">Senha</label>
                    <input :type="show ? 'text' : 'password'" name="senha" id="senha" class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    <!--<div class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">-->
                    <!--    <svg @click="show = !show" :class="{'hidden': show, 'block': !show }" class="h-5 w-5 text-gray-500 cursor-pointer" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">-->
                    <!--        <path fill="currentColor" d="M572.52 241.4C518.71 135.8 407.95 64 288 64 227.58 64 168.8 80.75 117.64 114.24L88.77 85.37C93.55 80.59 100.55 80.59 105.32 85.37L554.63 534.68C559.41 539.45 559.41 546.45 554.63 551.23L529.25 576.6C524.48 581.38 517.48 581.38 512.7 576.6L460.42 524.32C416.07 548.9 361.3 560 305.32 560 185.26 560 74.5 488.2 20.69 382.6-6.9 327.68-6.9 264.32 20.69 209.4 49.51 152.91 100.4 106.49 159.78 77.32L44.5 62.73C39.67 57.9 39.67 50.9 44.5 46.07L69.87 20.7C74.7 15.87 81.7 15.87 86.53 20.7L555.3 489.47C560.13 494.3 560.13 501.3 555.3 506.13L529.93 531.5C525.1 536.33 518.1 536.33 513.27 531.5L572.52 241.4zM288 480C387.48 480 474.74 424.23 523.31 336 492.11 279.61 441.16 233.19 381.78 204.02L331.6 153.85C341.64 150.43 352.08 148.64 362.89 148.64 463.36 148.64 550.62 203.41 599.19 291.64 626.78 346.56 626.78 409.92 599.19 464.84 545.38 570.44 434.62 642.24 314.56 642.24 254.14 642.24 195.36 625.49 144.2 591.99L114.8 621.4C109.96 626.23 102.96 626.23 98.13 621.4L72.76 596.03C67.93 591.2 67.93 584.2 72.76 579.37L136.23 515.9C159.37 530.39 184.29 541.47 210.79 548.77L132.44 470.42C83.84 443.9 44.5 393.7 20.69 333.9 3.1 289.18 3.1 242.82 20.69 198.1 74.5 92.5 185.26 20.7 305.32 20.7 365.74 20.7 424.52 37.45 475.68 70.95L493.22 53.41C498.05 48.58 505.05 48.58 509.88 53.41L535.25 78.78C540.08 83.61 540.08 90.61 535.25 95.44L288 480z"/>-->
                    <!--    </svg>-->
                    <!--    <svg @click="show = !show" :class="{'block': show, 'hidden': !show }" class="h-5 w-5 text-gray-500 cursor-pointer" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">-->
                    <!--        <path fill="currentColor" d="M320 96C202.7 96 99.5 158.1 49.47 256 99.5 353.9 202.7 416 320 416S540.5 353.9 590.5 256C540.5 158.1 437.3 96 320 96zM320 352C266.1 352 224 309.9 224 256S266.1 160 320 160 416 202.1 416 256 373.9 352 320 352z"/>-->
                    <!--    </svg>-->
                    <!--</div>-->
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-300">Entrar</button>
            </form>

            <p class="text-center mt-4 text-gray-600">
                Não tem sua autorização? <a href="registro.php" class="text-blue-600 hover:underline">Solicite aqui</a>
            </p>
        </div>
    </div>

</body>
</html>
