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
            }
            .no-print {
                display: none;
            }
            .container {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
            }
            .chart-container {
                break-inside: avoid;
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }
            .stat-card {
                break-inside: avoid;
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }
            .row {
                display: flex;
                flex-wrap: wrap;
                page-break-inside: avoid;
            }
            .charts-row {
                page-break-inside: avoid;
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

        <!-- Detalhamento por Tipo -->
        <div class="section-title">DETALHAMENTO POR TIPO DE SOLICITAÇÃO</div>
        <table class="detail-table">
            <tr>
                <td width="70%">Serviço de Atendimento ao Cidadão (SAC)</td>
                <td class="text-right"><strong><?php echo $sacTotal; ?></strong> solicitações</td>
            </tr>
            <tr>
                <td>Junta Administrativa de Recursos de Infrações (JARI)</td>
                <td class="text-right"><strong><?php echo $jariTotal; ?></strong> solicitações</td>
            </tr>
            <tr>
                <td>Cartão de Estacionamento (PCD)</td>
                <td class="text-right"><strong><?php echo $pcdTotal; ?></strong> solicitações</td>
            </tr>
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