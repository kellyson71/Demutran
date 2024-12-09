<?php
session_start();
include '../env/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Atualiza a senha do usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_atual = trim($_POST['senha_atual']);
    $nova_senha = trim($_POST['nova_senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);

    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro = 'As novas senhas não coincidem.';
    } else {
        // Validação da força da nova senha
        if (strlen($nova_senha) < 8) {
            $erro = 'A nova senha deve ter pelo menos 8 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $nova_senha)) {
            $erro = 'A nova senha deve conter pelo menos uma letra maiúscula.';
        } elseif (!preg_match('/[a-z]/', $nova_senha)) {
            $erro = 'A nova senha deve conter pelo menos uma letra minúscula.';
        } elseif (!preg_match('/[0-9]/', $nova_senha)) {
            $erro = 'A nova senha deve conter pelo menos um número.';
        } else {
            // Verifica a senha atual
            $sql = "SELECT senha FROM usuarios WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['usuario_id']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();

            if (password_verify($senha_atual, $usuario['senha'])) {
                // Atualiza a senha
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql_update = "UPDATE usuarios SET senha = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("si", $nova_senha_hash, $_SESSION['usuario_id']);
                if ($stmt_update->execute()) {
                    $sucesso = 'Senha atualizada com sucesso.';
                } else {
                    $erro = 'Erro ao atualizar senha.';
                }
            } else {
                $erro = 'Senha atual incorreta.';
            }
        }
    }
}



$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }">

<head>
    <meta charset="UTF-8">
    <title>Alterar Senha</title>
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
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <?php include 'topbar.php'; ?>
            <main class="flex-1 overflow-y-auto p-6 flex flex-col items-center">
                <!-- Formulário de Alteração de Senha -->
                <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Alterar Senha</h2>
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
                            <label for="senha_atual" class="block text-gray-700 font-medium mb-2">Senha Atual</label>
                            <input type="password" name="senha_atual" id="senha_atual"
                                class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
                                required>
                        </div>
                        <div class="mb-4">
                            <label for="nova_senha" class="block text-gray-700 font-medium mb-2">Nova Senha</label>
                            <input type="password" name="nova_senha" id="nova_senha"
                                class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
                                required>
                        </div>
                        <div class="mb-6">
                            <label for="confirmar_senha" class="block text-gray-700 font-medium mb-2">Confirmar Nova
                                Senha</label>
                            <input type="password" name="confirmar_senha" id="confirmar_senha"
                                class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600"
                                required>
                        </div>
                        <div class="flex space-x-4">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Alterar
                                Senha</button>
                            <a href="perfil.php"
                                class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400 transition">Cancelar</a>
                        </div>
                    </form>
                </div>
            </main>

            <!-- Footer -->
            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>

</html>