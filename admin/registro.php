<?php
session_start();
include 'config.php';

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
    <title>Registro - Administração</title>
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

    <div class="bg-white shadow-lg rounded-lg overflow-hidden w-full max-w-md">
        <div class="p-8">
            <h2 class="text-3xl font-semibold text-gray-800 text-center mb-4">Crie sua Conta</h2>
            <p class="text-gray-600 text-center mb-6">Preencha os campos abaixo para solicitar acesso</p>

            <?php if ($erro): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <form action="registro.php" method="POST" class="space-y-4">
                <div>
                    <label for="nome" class="block text-gray-700">Nome Completo</label>
                    <input type="text" name="nome" id="nome" class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>
                <div>
                    <label for="email" class="block text-gray-700">E-mail</label>
                    <input type="email" name="email" id="email" class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>
                <div x-data="{ show: false }" class="relative">
                    <label for="senha" class="block text-gray-700">Senha</label>
                    <input :type="show ? 'text' : 'password'" name="senha" id="senha" class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    <!--<div class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">-->
                    <!--    <svg @click="show = !show" :class="{'hidden': show, 'block': !show }" class="h-5 w-5 text-gray-500 cursor-pointer" fill="currentColor" viewBox="0 0 576 512">-->
                    <!--        <path d="M572.52 241.4C518.71 135.8 407.95 64 288 64 227.58 64 168.8 80.75 117.64 114.24L88.77 85.37C93.55 80.59 100.55 80.59 105.32 85.37L554.63 534.68C559.41 539.45 559.41 546.45 554.63 551.23L529.25 576.6C524.48 581.38 517.48 581.38 512.7 576.6L460.42 524.32C416.07 548.9 361.3 560 305.32 560 185.26 560 74.5 488.2 20.69 382.6-6.9 327.68-6.9 264.32 20.69 209.4 49.51 152.91 100.4 106.49 159.78 77.32L44.5 62.73C39.67 57.9 39.67 50.9 44.5 46.07L69.87 20.7C74.7 15.87 81.7 15.87 86.53 20.7L555.3 489.47C560.13 494.3 560.13 501.3 555.3 506.13L529.93 531.5C525.1 536.33 518.1 536.33 513.27 531.5L572.52 241.4zM288 480C387.48 480 474.74 424.23 523.31 336 492.11 279.61 441.16 233.19 381.78 204.02L331.6 153.85C341.64 150.43 352.08 148.64 362.89 148.64 463.36 148.64 550.62 203.41 599.19 291.64 626.78 346.56 626.78 409.92 599.19 464.84 545.38 570.44 434.62 642.24 314.56 642.24 254.14 642.24 195.36 625.49 144.2 591.99L114.8 621.4C109.96 626.23 102.96 626.23 98.13 621.4L72.76 596.03C67.93 591.2 67.93 584.2 72.76 579.37L136.23 515.9C159.37 530.39 184.29 541.47 210.79 548.77L132.44 470.42C83.84 443.9 44.5 393.7 20.69 333.9 3.1 289.18 3.1 242.82 20.69 198.1 74.5 92.5 185.26 20.7 305.32 20.7 365.74 20.7 424.52 37.45 475.68 70.95L493.22 53.41C498.05 48.58 505.05 48.58 509.88 53.41L535.25 78.78C540.08 83.61 540.08 90.61 535.25 95.44L288 480z"/>-->
                    <!--    </svg>-->
                    <!--    <svg @click="show = !show" :class="{'block': show, 'hidden': !show }" class="h-5 w-5 text-gray-500 cursor-pointer" fill="currentColor" viewBox="0 0 640 512">-->
                    <!--        <path d="M320 96C202.7 96 99.5 158.1 49.47 256 99.5 353.9 202.7 416 320 416S540.5 353.9 590.5 256C540.5 158.1 437.3 96 320 96zM320 352C266.1 352 224 309.9 224 256S266.1 160 320 160 416 202.1 416 256 373.9 352 320 352z"/>-->
                    <!--    </svg>-->
                    <!--</div>-->
                </div>
                <div x-data="{ show: false }" class="relative">
                    <label for="senha_confirmacao" class="block text-gray-700">Confirme a Senha</label>
                    <input :type="show ? 'text' : 'password'" name="senha_confirmacao" id="senha_confirmacao" class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    <!--<div class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">-->
                    <!--    <svg @click="show = !show" :class="{'hidden': show, 'block': !show }" class="h-5 w-5 text-gray-500 cursor-pointer" fill="currentColor" viewBox="0 0 576 512">-->
                    <!--        <path d="M572.52 241.4C518.71 135.8 407.95 64 288 64 227.58 64 168.8 80.75 117.64 114.24L88.77 85.37C93.55 80.59 100.55 80.59 105.32 85.37L554.63 534.68C559.41 539.45 559.41 546.45 554.63 551.23L529.25 576.6C524.48 581.38 517.48 581.38 512.7 576.6L460.42 524.32C416.07 548.9 361.3 560 305.32 560 185.26 560 74.5 488.2 20.69 382.6-6.9 327.68-6.9 264.32 20.69 209.4 49.51 152.91 100.4 106.49 159.78 77.32L44.5 62.73C39.67 57.9 39.67 50.9 44.5 46.07L69.87 20.7C74.7 15.87 81.7 15.87 86.53 20.7L555.3 489.47C560.13 494.3 560.13 501.3 555.3 506.13L529.93 531.5C525.1 536.33 518.1 536.33 513.27 531.5L572.52 241.4zM288 480C387.48 480 474.74 424.23 523.31 336 492.11 279.61 441.16 233.19 381.78 204.02L331.6 153.85C341.64 150.43 352.08 148.64 362.89 148.64 463.36 148.64 550.62 203.41 599.19 291.64 626.78 346.56 626.78 409.92 599.19 464.84 545.38 570.44 434.62 642.24 314.56 642.24 254.14 642.24 195.36 625.49 144.2 591.99L114.8 621.4C109.96 626.23 102.96 626.23 98.13 621.4L72.76 596.03C67.93 591.2 67.93 584.2 72.76 579.37L136.23 515.9C159.37 530.39 184.29 541.47 210.79 548.77L132.44 470.42C83.84 443.9 44.5 393.7 20.69 333.9 3.1 289.18 3.1 242.82 20.69 198.1 74.5 92.5 185.26 20.7 305.32 20.7 365.74 20.7 424.52 37.45 475.68 70.95L493.22 53.41C498.05 48.58 505.05 48.58 509.88 53.41L535.25 78.78C540.08 83.61 540.08 90.61 535.25 95.44L288 480z"/>-->
                    <!--    </svg>-->
                    <!--    <svg @click="show = !show" :class="{'block': show, 'hidden': !show }" class="h-5 w-5 text-gray-500 cursor-pointer" fill="currentColor" viewBox="0 0 640 512">-->
                    <!--        <path d="M320 96C202.7 96 99.5 158.1 49.47 256 99.5 353.9 202.7 416 320 416S540.5 353.9 590.5 256C540.5 158.1 437.3 96 320 96zM320 352C266.1 352 224 309.9 224 256S266.1 160 320 160 416 202.1 416 256 373.9 352 320 352z"/>-->
                    <!--    </svg>-->
                    <!--</div>-->
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-300">Registrar</button>
            </form>

            <p class="text-center mt-4 text-gray-600">
                Já tem uma conta? <a href="login.php" class="text-blue-600 hover:underline">Faça login</a>
            </p>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Cadastro Pendente de Aprovação</h2>
            <p>Seu cadastro foi recebido com sucesso. Após aprovação, você receberá uma notificação por e-mail.</p>
            <div class="mt-6 flex justify-end">
                <button onclick="closeModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Fechar</button>
            </div>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('confirmationModal').classList.add('hidden');
        }
    </script>

</body>
</html>
