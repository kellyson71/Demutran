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

// Consultas aprimoradas para estatísticas com subcategorias
$defesaStats = $conn->query("
    SELECT 
        tipo_solicitacao,
        COUNT(*) as total 
    FROM solicitacoes_demutran 
    GROUP BY tipo_solicitacao
")->fetch_all(MYSQLI_ASSOC);

$pcdStats = $conn->query("
    SELECT 
        tipo_solicitacao,
        COUNT(*) as total 
    FROM solicitacao_cartao 
    GROUP BY tipo_solicitacao
")->fetch_all(MYSQLI_ASSOC);

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

function renderEmptyStateCard($title, $message = 'Nenhum dado disponível para exibição') {
    ?>
    <div class="bg-white rounded-lg p-6 shadow-sm">
        <div class="flex flex-col items-center justify-center py-8">
            <span class="material-icons text-gray-400 text-5xl mb-4">analytics_off</span>
            <h3 class="text-lg font-medium text-gray-900 mb-2"><?php echo $title; ?></h3>
            <p class="text-gray-500 text-center"><?php echo $message; ?></p>
        </div>
    </div>
    <?php
}

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

                <!-- Adicionar após o header e antes dos cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <?php if ($sacFormularios['total'] == 0 && $jariFormularios['total'] == 0 && 
                          $pcdFormularios['total'] == 0 && $dat4Formularios['total'] == 0 && 
                          $parecerFormularios['total'] == 0) : ?>
        
                        <?php renderEmptyStateCard('Sem Dados Disponíveis', 'Nenhum formulário foi registrado ainda no sistema.'); ?>
        
                    <?php else : ?>
                        <!-- Cards existentes -->
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Total de Solicitações</p>
                                    <h3 class="text-2xl font-bold text-gray-900">
                                        <?php echo $periodoAtual; ?>
                                    </h3>
                                </div>
                                <div class="bg-blue-100 rounded-full p-3">
                                    <span class="material-icons text-blue-600">assessment</span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="text-sm <?php echo $variacao >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $variacao >= 0 ? '↑' : '↓'; ?> <?php echo abs($variacao); ?>%
                                </span>
                                <span class="text-sm text-gray-500 ml-1">vs mês anterior</span>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Status das Solicitações</p>
                                    <h3 class="text-2xl font-bold text-gray-900">100%</h3>
                                </div>
                                <div class="bg-blue-100 rounded-full p-3">
                                    <span class="material-icons text-blue-600">pending_actions</span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: 100%"></div>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Em processamento</p>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Média Diária</p>
                                    <h3 class="text-2xl font-bold text-gray-900">
                                        <?php echo round($periodoAtual / 22, 1); ?>
                                    </h3>
                                </div>
                                <div class="bg-purple-100 rounded-full p-3">
                                    <span class="material-icons text-purple-600">speed</span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="text-sm text-gray-500">Base: 22 dias úteis</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Nova estrutura de Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Card de Defesas/Solicitações -->
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <div class="flex items-center mb-4">
                            <span class="material-icons text-blue-600 mr-2">gavel</span>
                            <h2 class="text-lg font-bold text-gray-800">Solicitações e Defesas</h2>
                        </div>
                        <div class="space-y-4">
                            <?php
                            $defesaIcons = [
                                'apresentacao_condutor' => ['person', 'Apresentação de Condutor', 'bg-blue-600'],
                                'defesa_previa' => ['description', 'Defesa Prévia', 'bg-blue-500'],
                                'jari' => ['balance', 'Recurso JARI', 'bg-blue-400']
                            ];
                            foreach ($defesaStats as $stat): 
                                $icon = $defesaIcons[$stat['tipo_solicitacao']][0];
                                $label = $defesaIcons[$stat['tipo_solicitacao']][1];
                                $bgColor = $defesaIcons[$stat['tipo_solicitacao']][2];
                            ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        <div class="<?php echo $bgColor; ?> p-2 rounded-lg">
                                            <span class="material-icons text-white"><?php echo $icon; ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-900"><?php echo $label; ?></span>
                                            <div class="text-sm text-gray-500">Protocolo DEMUTRAN</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-semibold text-blue-600"><?php echo $stat['total']; ?></span>
                                        <span class="text-sm text-gray-500">registros</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Card de PCD/Idoso -->
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <div class="flex items-center mb-4">
                            <span class="material-icons text-purple-600 mr-2">accessible</span>
                            <h2 class="text-lg font-bold text-gray-800">Credenciais Especiais</h2>
                        </div>
                        <div class="space-y-4">
                            <?php
                            $pcdIcons = [
                                'pcd' => ['accessible', 'Pessoa com Deficiência', 'bg-purple-600'],
                                'idoso' => ['elderly', 'Credencial para Idoso', 'bg-purple-500']
                            ];
                            foreach ($pcdStats as $stat): 
                                $icon = $pcdIcons[$stat['tipo_solicitacao']][0];
                                $label = $pcdIcons[$stat['tipo_solicitacao']][1];
                                $bgColor = $pcdIcons[$stat['tipo_solicitacao']][2];
                            ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        <div class="<?php echo $bgColor; ?> p-2 rounded-lg">
                                            <span class="material-icons text-white"><?php echo $icon; ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-900"><?php echo $label; ?></span>
                                            <div class="text-sm text-gray-500">Cartão de Estacionamento</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-semibold text-purple-600"><?php echo $stat['total']; ?></span>
                                        <span class="text-sm text-gray-500">emitidos</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid (modificado para incluir subcategorias) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php if ($sacFormularios['total'] == 0 && $jariFormularios['total'] == 0 && 
                          $pcdFormularios['total'] == 0 && $dat4Formularios['total'] == 0 && 
                          $parecerFormularios['total'] == 0) : ?>
        
                        <div class="col-span-2">
                            <?php renderEmptyStateCard('Gráficos Indisponíveis', 'Os gráficos serão exibidos quando houver dados para análise.'); ?>
                        </div>
        
                    <?php else : ?>
                        <!-- Gráficos existentes -->
                        <div class="bg-white rounded-xl p-6 shadow-sm">
                            <h2 class="text-lg font-bold text-gray-800 mb-4">Distribuição de Serviços</h2>
                            <canvas id="servicesChart"></canvas>
                        </div>

                        <div class="bg-white rounded-xl p-6 shadow-sm">
                            <h2 class="text-lg font-bold text-gray-800 mb-4">Tendência Mensal</h2>
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        <?php if ($sacFormularios['total'] > 0 || $jariFormularios['total'] > 0 || 
              $pcdFormularios['total'] > 0 || $dat4Formularios['total'] > 0 || 
              $parecerFormularios['total'] > 0) : ?>
    
        // Gráfico de Distribuição de Serviços
        new Chart(document.getElementById('servicesChart'), {
            type: 'doughnut',
            data: {
                labels: [
                    'Apresentação de Condutor',
                    'Defesa Prévia',
                    'Recurso JARI',
                    'PCD',
                    'Idoso',
                    'SAC',
                    'DAT',
                    'Parecer'
                ],
                datasets: [{
                    data: [
                        <?php 
                        $defesaCounters = array_column($defesaStats, 'total', 'tipo_solicitacao');
                        $pcdCounters = array_column($pcdStats, 'total', 'tipo_solicitacao');
                        
                        echo $defesaCounters['apresentacao_condutor'] ?? 0; ?>,
                        <?php echo $defesaCounters['defesa_previa'] ?? 0; ?>,
                        <?php echo $defesaCounters['jari'] ?? 0; ?>,
                        <?php echo $pcdCounters['pcd'] ?? 0; ?>,
                        <?php echo $pcdCounters['idoso'] ?? 0; ?>,
                        <?php echo $sacFormularios['total']; ?>,
                        <?php echo $dat4Formularios['total']; ?>,
                        <?php echo $parecerFormularios['total']; ?>
                    ],
                    backgroundColor: [
                        '#3B82F6', // Azul
                        '#60A5FA', // Azul claro
                        '#93C5FD', // Azul mais claro
                        '#8B5CF6', // Roxo
                        '#A78BFA', // Roxo claro
                        '#F59E0B', // Laranja
                        '#EF4444', // Vermelho
                        '#10B981'  // Verde
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 20
                        }
                    }
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

        <?php endif; ?>

        function downloadReport() {
            // Redireciona para a página de geração do relatório
            window.location.href = '../utils/form/gerar_relatorio.php?type=analytics';
        }
    </script>
</body>
</html>