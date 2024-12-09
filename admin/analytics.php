<?php
session_start();
include '../env/config.php';
include './includes/template.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

// Consultas para estatísticas
$sacFormularios = $conn->query("SELECT COUNT(*) as total FROM sac")->fetch_assoc();
$jariFormularios = $conn->query("SELECT COUNT(*) as total FROM solicitacoes_demutran")->fetch_assoc();
$pcdFormularios = $conn->query("SELECT COUNT(*) as total FROM solicitacao_cartao")->fetch_assoc();
$dat4Formularios = $conn->query("SELECT COUNT(*) as total FROM DAT4")->fetch_assoc();
$parecerFormularios = $conn->query("SELECT COUNT(*) as total FROM Parecer")->fetch_assoc();

// Estatísticas mensais
$monthlyStats = $conn->query("
    SELECT 
        DATE_FORMAT(data_submissao, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN tipo = 'sac' THEN 1 ELSE 0 END) as sac,
        SUM(CASE WHEN tipo = 'jari' THEN 1 ELSE 0 END) as jari,
        SUM(CASE WHEN tipo = 'pcd' THEN 1 ELSE 0 END) as pcd,
        SUM(CASE WHEN tipo = 'dat' THEN 1 ELSE 0 END) as dat,
        SUM(CASE WHEN tipo = 'parecer' THEN 1 ELSE 0 END) as parecer
    FROM (
        SELECT data_submissao, 'sac' as tipo FROM sac
        UNION ALL
        SELECT data_submissao, 'jari' as tipo FROM solicitacoes_demutran
        UNION ALL
        SELECT data_submissao, 'pcd' as tipo FROM solicitacao_cartao
        UNION ALL
        SELECT data_submissao, 'dat' as tipo FROM DAT4
        UNION ALL
        SELECT data_submissao, 'parecer' as tipo FROM Parecer
    ) AS combined
    WHERE data_submissao >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(data_submissao, '%Y-%m')
    ORDER BY month ASC
");

?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }">
<head>
    <meta charset="UTF-8">
    <title>Análise de Dados - Painel Administrativo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 flex flex-col h-full">
                <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                <?php echo getSidebarHtml('analytics'); ?>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php 
            $topbarHtml = getTopbarHtml('Análise de Dados', $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Header -->
                <div class="mb-8">
                    <div class="relative overflow-hidden bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-8 mb-6">
                        <div class="relative z-10 flex justify-between items-start">
                            <div>
                                <h1 class="text-3xl font-bold text-white mb-2">Estatísticas e Análises</h1>
                                <p class="text-blue-100">Visualização detalhada do desempenho e métricas do sistema</p>
                            </div>
                            <button onclick="downloadReport()" class="bg-white text-blue-600 px-4 py-2 rounded-lg shadow-sm hover:bg-blue-50 flex items-center">
                                <span class="material-icons mr-2">download</span>
                                Baixar Relatório
                            </button>
                        </div>
                        <div class="absolute right-0 top-0 transform translate-x-1/4 -translate-y-1/4">
                            <span class="material-icons text-blue-400 opacity-50" style="font-size: 196px;">analytics</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Distribuição de Serviços -->
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Distribuição de Serviços</h2>
                        <canvas id="servicesChart"></canvas>
                    </div>

                    <!-- Tendência Mensal -->
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Tendência Mensal</h2>
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Gráfico de Distribuição de Serviços
        new Chart(document.getElementById('servicesChart'), {
            type: 'doughnut',
            data: {
                labels: ['SAC', 'JARI', 'PCD', 'DAT', 'Parecer'],
                datasets: [{
                    data: [
                        <?php echo $sacFormularios['total']; ?>,
                        <?php echo $jariFormularios['total']; ?>,
                        <?php echo $pcdFormularios['total']; ?>,
                        <?php echo $dat4Formularios['total']; ?>,
                        <?php echo $parecerFormularios['total']; ?>
                    ],
                    backgroundColor: ['#3B82F6', '#F59E0B', '#8B5CF6', '#EF4444', '#10B981']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Dados mensais
        <?php
        $labels = [];
        $datasets = [
            'sac' => ['label' => 'SAC', 'data' => [], 'borderColor' => '#3B82F6'],
            'jari' => ['label' => 'JARI', 'data' => [], 'borderColor' => '#F59E0B'],
            'pcd' => ['label' => 'PCD', 'data' => [], 'borderColor' => '#8B5CF6'],
            'dat' => ['label' => 'DAT', 'data' => [], 'borderColor' => '#EF4444'],
            'parecer' => ['label' => 'Parecer', 'data' => [], 'borderColor' => '#10B981']
        ];

        while ($row = $monthlyStats->fetch_assoc()) {
            $labels[] = date('M/Y', strtotime($row['month']));
            foreach ($datasets as $key => &$dataset) {
                $dataset['data'][] = $row[$key];
            }
        }
        ?>

        // Gráfico de Tendência Mensal
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: <?php echo json_encode(array_values($datasets)); ?>
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Mais gráficos podem ser adicionados aqui...

        function downloadReport() {
            // Redireciona para a página de geração do relatório
            window.location.href = '../utils/form/gerar_relatorio.php?type=analytics';
        }
    </script>
</body>
</html>