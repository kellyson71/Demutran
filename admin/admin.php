<?php
session_start();
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

function contarNotificacoesNaoLidas($conn) {
    $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE lida = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

function obterUltimosFormularios($conn, $tabela, $limite = 5) {
    $sql = "SELECT * FROM $tabela ORDER BY id DESC LIMIT $limite";
    return $conn->query($sql);
}

$sacFormularios = obterUltimosFormularios($conn, 'sac');
$jariFormularios = obterUltimosFormularios($conn, 'solicitacoes_demutran');
$pcdFormularios = obterUltimosFormularios($conn, 'solicitacao_cartao');
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }" x-init="$refs.loading.classList.add('hidden')">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
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
    <div x-ref="loading" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
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

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Welcome Banner -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?>!</h1>
                    <p class="text-gray-600 mt-2">Aqui está o resumo das atividades recentes.</p>
                </div>

                <!-- Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <span class="material-icons text-blue-600 text-4xl">assignment</span>
                            <div class="ml-4">
                                <p class="text-gray-600">Formulários SAC</p>
                                <p class="text-2xl font-bold"><?php echo $sacFormularios->num_rows; ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <span class="material-icons text-green-600 text-4xl">how_to_vote</span>
                            <div class="ml-4">
                                <p class="text-gray-600">Defesas JARI</p>
                                <p class="text-2xl font-bold"><?php echo $jariFormularios->num_rows; ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <span class="material-icons text-purple-600 text-4xl">accessible</span>
                            <div class="ml-4">
                                <p class="text-gray-600">Cartões PCD</p>
                                <p class="text-2xl font-bold"><?php echo $pcdFormularios->num_rows; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Submissions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Recent JARI Forms -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Últimas Defesas JARI</h2>
                        <?php while($formulario = $jariFormularios->fetch_assoc()): ?>
                            <div class="border-b py-2">
                                <a href="detalhes_formulario.php?id=<?php echo $formulario['id']; ?>&tipo=JARI" class="flex justify-between items-center">
                                    <div>
                                        <p class="text-gray-700 font-medium"><?php echo $formulario['nome']; ?></p>
                                        <p class="text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($formulario['data_submissao'])); ?></p>
                                    </div>
                                    <span class="material-icons text-gray-400">chevron_right</span>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Recent PCD Forms -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Últimos Formulários PCD</h2>
                        <?php while($formulario = $pcdFormularios->fetch_assoc()): ?>
                            <div class="border-b py-2">
                                <a href="detalhes_formulario.php?id=<?php echo $formulario['id']; ?>&tipo=PCD" class="flex justify-between items-center">
                                    <div>
                                        <p class="text-gray-700 font-medium"><?php echo $formulario['solicitante']; ?></p>
                                        <p class="text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($formulario['data_submissao'])); ?></p>
                                    </div>
                                    <span class="material-icons text-gray-400">chevron_right</span>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>
</html>
