<?php
session_start();
include '../../env/config.php';

// Buscar dados analíticos do banco
$sacTotal = $conn->query("SELECT COUNT(*) as total FROM sac")->fetch_assoc()['total'];
$jariTotal = $conn->query("SELECT COUNT(*) as total FROM solicitacoes_demutran")->fetch_assoc()['total'];
$pcdTotal = $conn->query("SELECT COUNT(*) as total FROM solicitacao_cartao")->fetch_assoc()['total'];
$datTotal = $conn->query("SELECT COUNT(*) as total FROM DAT4")->fetch_assoc()['total'];
$parecerTotal = $conn->query("SELECT COUNT(*) as total FROM Parecer")->fetch_assoc()['total'];

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
    WHERE data_submissao >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(data_submissao, '%Y-%m')
    ORDER BY month ASC
");

// Preparar dados para os gráficos
$labels = [];
$datasets = [];
while ($row = $monthlyStats->fetch_assoc()) {
    $labels[] = date('M/Y', strtotime($row['month']));
    $datasets['sac'][] = $row['sac'];
    $datasets['jari'][] = $row['jari'];
    $datasets['pcd'][] = $row['pcd'];
    $datasets['dat'][] = $row['dat'];
    $datasets['parecer'][] = $row['parecer'];
}

// Adicionar queries para status e comparações
$statusQuery = $conn->query("
    SELECT 
        CASE 
            WHEN situacao IS NULL THEN 'Pendente'
            ELSE situacao 
        END as status,
        COUNT(*) as total,
        ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 1) as percentual
    FROM (
        SELECT situacao FROM sac
        UNION ALL SELECT situacao FROM solicitacoes_demutran
        UNION ALL SELECT situacao FROM solicitacao_cartao
        UNION ALL SELECT situacao FROM DAT4
        UNION ALL SELECT situacao FROM Parecer
    ) as combined
    GROUP BY status
");

// Se mesmo assim der erro, use esta versão simplificada
if (!$statusQuery) {
    $statusQuery = $conn->query("
        SELECT 
            status,
            total,
            ROUND((total * 100.0 / (SELECT COUNT(*) FROM (
                SELECT 1 FROM sac
                UNION ALL SELECT 1 FROM solicitacoes_demutran
                UNION ALL SELECT 1 FROM solicitacao_cartao
                UNION ALL SELECT 1 FROM DAT4
                UNION ALL SELECT 1 FROM Parecer
            ) t)), 1) as percentual
        FROM (
            SELECT 
                'Em Processamento' as status,
                COUNT(*) as total
            FROM (
                SELECT 'sac' FROM sac
                UNION ALL SELECT 'jari' FROM solicitacoes_demutran
                UNION ALL SELECT 'pcd' FROM solicitacao_cartao
                UNION ALL SELECT 'dat' FROM DAT4
                UNION ALL SELECT 'parecer' FROM Parecer
            ) t
        ) stats
    ");
}

// Ajustar a exibição do status na view
$statusData = [];
if ($statusQuery) {
    while ($row = $statusQuery->fetch_assoc()) {
        $statusData[] = $row;
    }
} else {
    // Dados padrão caso não haja informação de status
    $statusData = [
        ['status' => 'Em Processamento', 'total' => $sacTotal + $jariTotal + $pcdTotal + $datTotal + $parecerTotal, 'percentual' => 100]
    ];
}

// Comparação com período anterior
$periodoAtual = $conn->query("
    SELECT COUNT(*) as total 
    FROM (
        SELECT data_submissao FROM sac
        UNION ALL SELECT data_submissao FROM solicitacoes_demutran
        UNION ALL SELECT data_submissao FROM solicitacao_cartao
        UNION ALL SELECT data_submissao FROM DAT4
        UNION ALL SELECT data_submissao FROM Parecer
    ) as combined
    WHERE data_submissao >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
")->fetch_assoc()['total'];

$periodoAnterior = $conn->query("
    SELECT COUNT(*) as total 
    FROM (
        SELECT data_submissao FROM sac
        UNION ALL SELECT data_submissao FROM solicitacoes_demutran
        UNION ALL SELECT data_submissao FROM solicitacao_cartao
        UNION ALL SELECT data_submissao FROM DAT4
        UNION ALL SELECT data_submissao FROM Parecer
    ) as combined
    WHERE data_submissao BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 2 MONTH) AND DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
")->fetch_assoc()['total'];

$variacao = $periodoAnterior > 0 ? round(($periodoAtual - $periodoAnterior) * 100 / $periodoAnterior, 1) : 100;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório Analítico - DEMUTRAN</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f8f9fa;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        .logo-container {
            position: relative;
            text-align: center;
            margin-bottom: 1rem;
            padding: 1rem 0;
        }
        .logo {
            height: 60px; /* Reduzido de 80px */
            margin: 0 15px;
        }
        .header-title {
            text-align: center;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .header-title p {
            margin: 0;
            line-height: 1.2;
        }
        .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            margin-bottom: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        .stat-number {
            font-size: 1.5rem; /* Reduzido de 2rem */
            font-weight: bold;
            color: #2196F3;
        }
        .chart-container {
            background: #fff;
            border-radius: 8px;
            padding: 0.75rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            height: 180px; /* Reduzido de 250px para 180px */
            width: 100%; /* Garante que ocupe toda a largura disponível */
        }
        .charts-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .chart-wrapper {
            flex: 1;
            min-width: 0; /* Previne overflow em flex items */
        }
        @media print {
            body {
                background: #fff;
                width: 100%;
                margin: 0;
                padding: 15px;
            }
            .no-print {
                display: none;
            }
            .container {
                width: 100% !important;
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            /* Corrigir para manter layout em grid */
            .row {
                display: flex !important; /* Forçar flex */
                flex-wrap: wrap !important;
                width: 100% !important;
                page-break-inside: avoid;
            }
            
            /* Manter colunas lado a lado */
            .col-md-3 {
                width: 25% !important;
                flex: 0 0 25% !important;
            }
            .col-md-4 {
                width: 33.333% !important;
                flex: 0 0 33.333% !important;
            }
            .col-md-6 {
                width: 50% !important;
                flex: 0 0 50% !important;
            }
            
            /* Manter gráficos lado a lado */
            .charts-row {
                display: flex !important;
                gap: 1rem !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .chart-wrapper {
                width: 50% !important;
                flex: 1 !important;
            }
            .chart-container {
                height: 200px !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            /* Ajustes para melhorar espaçamento */
            .stat-card, .detail-table, .table-responsive {
                margin-bottom: 15px !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            /* Garantir que cores e backgrounds sejam impressos */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 0.75rem;
            margin-top: 1rem;
            font-weight: bold;
            border-left: 4px solid #2196F3;
            font-size: 0.9rem;
            break-inside: avoid;
        }
        
        .detail-table {
            width: 100%;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .detail-table td {
            padding: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .detail-table tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4"> <!-- Reduzido padding -->
        <div class="logo-container">
            <img src="./image1.png" alt="Logo Esquerda" class="logo">
            <img src="./image3.png" alt="Logo Direita" class="logo">
        </div>
        
        <div class="header-title">
            <p>Estado do Rio Grande do Norte</p>
            <p>Prefeitura Municipal de Pau dos Ferros</p>
            <p>Secretaria de Governo – SEGOV</p>
            <p>Departamento Municipal de Trânsito – DEMUTRAN</p>
        </div>

        <div class="text-center mb-3"> <!-- Reduzido margin -->
            <h2 class="h4">Relatório Analítico de Solicitações</h2>
            <p class="text-muted small">Gerado em <?php echo date('d/m/Y H:i'); ?></p>
            <button onclick="window.print()" class="btn btn-sm btn-primary no-print mt-2">
                <i class="fas fa-print"></i> Imprimir Relatório
            </button>
        </div>

        <!-- Adicionar novas métricas e comparações -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <small class="text-muted">Solicitações (Últimos 30 dias)</small>
                    <div class="stat-number"><?php echo $periodoAtual; ?></div>
                    <div class="small <?php echo $variacao >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $variacao >= 0 ? '+' : ''; echo $variacao; ?>% vs período anterior
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <small class="text-muted">Média por Dia Útil</small>
                    <div class="stat-number"><?php echo round($periodoAtual / 22, 1); ?></div>
                    <div class="small text-muted">Base: 22 dias úteis</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <small class="text-muted">Status das Solicitações</small>
                    <div class="progress mt-2" style="height: 20px;">
                        <?php
                        $cores = [
                            'Concluído' => 'bg-success',
                            'Em Andamento' => 'bg-warning',
                            'Em Processamento' => 'bg-info',
                            'Pendente' => 'bg-secondary'
                        ];
                        foreach ($statusData as $status) {
                            $cor = $cores[$status['status']] ?? 'bg-secondary';
                            echo "<div class='{$cor}' style='width: {$status['percentual']}%' 
                                  title='{$status['status']}: {$status['total']} ({$status['percentual']}%)'>
                                  </div>";
                        }
                        ?>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <?php
                        foreach ($statusData as $status) {
                            echo "<small>{$status['status']}: {$status['percentual']}%</small>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalhamento por Tipo -->
        <div class="section-title">DETALHAMENTO POR TIPO DE SOLICITAÇÃO</div>
        <table class="detail-table">
            <tr>
                <td width="70%">Serviço de Atendimento ao Cidadão (SAC)</td>
                <td class="text-right"><strong><?php echo $sacTotal; ?></strong> solicitações</td>
            </tr>
            
            <!-- Grupo JARI/Defesas com subcategorias -->
            <tr style="background-color: #f8f9fa;">
                <td colspan="2"><strong>Solicitações e Defesas</strong></td>
            </tr>
            <?php
            $defesaStats = $conn->query("SELECT tipo_solicitacao, COUNT(*) as total FROM solicitacoes_demutran GROUP BY tipo_solicitacao");
            $tipos = [
                'apresentacao_condutor' => 'Apresentação de Condutor',
                'defesa_previa' => 'Defesa Prévia',
                'jari' => 'Recurso JARI'
            ];
            while ($row = $defesaStats->fetch_assoc()) {
                echo "<tr>
                    <td style='padding-left: 2rem;'>" . $tipos[$row['tipo_solicitacao']] . "</td>
                    <td class='text-right'><strong>" . $row['total'] . "</strong> solicitações</td>
                </tr>";
            }
            ?>
            
            <!-- Grupo PCD/Idoso com subcategorias -->
            <tr style="background-color: #f8f9fa;">
                <td colspan="2"><strong>Credenciais Especiais</strong></td>
            </tr>
            <?php
            $pcdStats = $conn->query("SELECT tipo_solicitacao, COUNT(*) as total FROM solicitacao_cartao GROUP BY tipo_solicitacao");
            $tiposPcd = [
                'pcd' => 'Pessoa com Deficiência (PCD)',
                'idoso' => 'Credencial para Idoso'
            ];
            while ($row = $pcdStats->fetch_assoc()) {
                echo "<tr>
                    <td style='padding-left: 2rem;'>" . $tiposPcd[$row['tipo_solicitacao']] . "</td>
                    <td class='text-right'><strong>" . $row['total'] . "</strong> solicitações</td>
                </tr>";
            }
            ?>
            
            <tr>
                <td>Declaração de Acidente de Trânsito (DAT)</td>
                <td class="text-right"><strong><?php echo $datTotal; ?></strong> solicitações</td>
            </tr>
            <tr>
                <td>Pareceres Técnicos</td>
                <td class="text-right"><strong><?php echo $parecerTotal; ?></strong> solicitações</td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td><strong>Total Geral</strong></td>
                <td class="text-right"><strong><?php echo $sacTotal + $jariTotal + $pcdTotal + $datTotal + $parecerTotal; ?></strong> solicitações</td>
            </tr>
        </table>

        <!-- Cards de Estatísticas em linha única -->
        <div class="section-title">RESUMO ESTATÍSTICO</div>
        <div class="row g-2 mb-3"> <!-- Reduzido gap e margin -->
            <div class="col-md-4">
                <div class="stat-card">
                    <h6 class="mb-1">Total de Solicitações</h6>
                    <div class="stat-number"><?php echo $sacTotal + $jariTotal + $pcdTotal + $datTotal + $parecerTotal; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h6 class="mb-1">Solicitações este Mês</h6>
                    <div class="stat-number"><?php echo end($datasets['sac']) + end($datasets['jari']) + end($datasets['pcd']) + end($datasets['dat']) + end($datasets['parecer']); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h6 class="mb-1">Média Mensal</h6>
                    <div class="stat-number"><?php echo round(($sacTotal + $jariTotal + $pcdTotal + $datTotal + $parecerTotal) / 6); ?></div>
                </div>
            </div>
        </div>

        <!-- Adicionar seção de Comparativos -->
        <div class="section-title">ANÁLISE COMPARATIVA</div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Tipo de Solicitação</th>
                        <th class="text-right">Mês Atual</th>
                        <th class="text-right">Mês Anterior</th>
                        <th class="text-right">Variação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tiposSolicitacao = [
                        'SAC' => ['atual' => end($datasets['sac']), 'anterior' => prev($datasets['sac'])],
                        'JARI' => ['atual' => end($datasets['jari']), 'anterior' => prev($datasets['jari'])],
                        'PCD' => ['atual' => end($datasets['pcd']), 'anterior' => prev($datasets['pcd'])],
                        'DAT' => ['atual' => end($datasets['dat']), 'anterior' => prev($datasets['dat'])],
                        'Parecer' => ['atual' => end($datasets['parecer']), 'anterior' => prev($datasets['parecer'])]
                    ];

                    foreach ($tiposSolicitacao as $tipo => $dados) {
                        $variacao = $dados['anterior'] > 0 ? 
                            round(($dados['atual'] - $dados['anterior']) * 100 / $dados['anterior'], 1) : 100;
                        $variacaoClass = $variacao >= 0 ? 'text-success' : 'text-danger';
                        
                        echo "<tr>
                            <td>{$tipo}</td>
                            <td class='text-right'>{$dados['atual']}</td>
                            <td class='text-right'>{$dados['anterior']}</td>
                            <td class='text-right {$variacaoClass}'>" . 
                            ($variacao >= 0 ? '+' : '') . "{$variacao}%</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Gráficos em 2 colunas -->
        <div class="section-title">VISUALIZAÇÃO GRÁFICA</div>
        <div class="charts-row">
            <div class="chart-wrapper">
                <div class="chart-container">
                    <h6 class="mb-2" style="font-size: 0.8rem;">Distribuição de Serviços</h6>
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
            <div class="chart-wrapper">
                <div class="chart-container">
                    <h6 class="mb-2" style="font-size: 0.8rem;">Evolução Mensal</h6>
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configurações comuns dos gráficos
        Chart.defaults.font.size = 9; // Reduzido de 11
        Chart.defaults.plugins.legend.labels.boxWidth = 8; // Reduzido de 12
        
        // Gráfico de Distribuição
        new Chart(document.getElementById('distributionChart'), {
            type: 'doughnut',
            data: {
                labels: ['SAC', 'JARI', 'PCD', 'DAT', 'Parecer'],
                datasets: [{
                    data: [<?php echo "$sacTotal, $jariTotal, $pcdTotal, $datTotal, $parecerTotal"; ?>],
                    backgroundColor: ['#3B82F6', '#F59E0B', '#8B5CF6', '#EF4444', '#10B981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 8 }, // Reduzido de 10
                            padding: 8 // Reduzido padding
                        }
                    }
                }
            }
        });

        // Gráfico de Evolução
        new Chart(document.getElementById('evolutionChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Total de Solicitações',
                    data: <?php echo json_encode(array_map(function($sac, $jari, $pcd, $dat, $parecer) {
                        return $sac + $jari + $pcd + $dat + $parecer;
                    }, $datasets['sac'], $datasets['jari'], $datasets['pcd'], $datasets['dat'], $datasets['parecer'])); ?>,
                    borderColor: '#2196F3',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 8 }, // Reduzido de 10
                            padding: 4 // Reduzido padding
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            stepSize: 1,
                            font: { size: 8 } // Reduzido de 10
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 8 } // Reduzido de 10
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>