<?php
// Verifique se a sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Verificação de login do usuário
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Função para contar notificações, verificando se já não está definida
if (!function_exists('contarNotificacoesNaoLidas')) {
    function contarNotificacoesNaoLidas($conn) {
        $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE lida = 0";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }" x-init="$refs.loading.classList.add('hidden')">
<head>
    <!-- Cabeçalho -->
    <meta charset="UTF-8">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina : 'Painel Administrativo'; ?></title>
    <!-- ... demais tags ... -->
</head>
<body class="bg-gray-100 font-roboto">
    <!-- Loader -->
    <!-- ... -->
    <!-- Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <?php include 'topbar.php'; ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6">
                <?php
                // Exibe o conteúdo específico da página
                echo $content;
                ?>
            </main>

            <!-- Footer -->
            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>
</html>
