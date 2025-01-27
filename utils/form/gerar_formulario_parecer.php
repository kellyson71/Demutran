<?php
session_start();
require_once '../../env/config.php';
require_once '../../components/print-components.php';

// Obter ID da URL
$id = $_GET['id'] ?? '';

if (empty($id)) {
    die('ID não fornecido');
}

// Preparar e executar consulta
$sql = "SELECT * FROM Parecer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$dados = $result->fetch_assoc();

if (!$dados) {
    die('Parecer não encontrado');
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Parecer Técnico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Estilos base comuns */
        body {
            margin: 20px 0;
            background: white;
        }

        .container {
            max-width: 800px;
            background: white;
            padding: 20px;
            min-height: 29.7cm;
            width: 21cm;
            margin: 0 auto;
        }

        /* Logos e cabeçalho */
        .logo-container {
            position: relative;
            height: 120px;
            margin-bottom: 40px;
        }

        .logo {
            position: absolute;
            top: 0;
            max-width: 80px;
            height: auto;
        }

        .logo-left {
            left: 0;
        }

        .logo-right {
            right: 0;
        }

        /* Título centralizado */
        .centered-title {
            text-align: center;
            padding: 0 100px;
            margin-top: 20px;
        }

        .centered-title p {
            margin: 2px 0;
            line-height: 1.4;
        }

        /* Número do protocolo */
        .protocol-number {
            text-align: right;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }

        .protocol-number span {
            border-bottom: 1px solid #000;
            padding: 2px 5px;
        }

        /* Seções e tabelas */
        .section-title {
            background-color: #E3F2FD;
            padding: 8px 15px;
            margin: 20px 0 10px 0;
            font-weight: bold;
            border-left: 4px solid #2196F3;
        }

        .data-table {
            width: 100%;
            margin: 10px 0;
        }

        .data-table td {
            padding: 6px;
            vertical-align: top;
        }

        /* Assinaturas */
        .signature-section {
            margin-top: 50px;
            text-align: center;
        }

        .signature-line {
            width: 60%;
            border-top: 1px solid #000;
            margin: 0 auto 5px;
        }

        /* Ajustes para impressão */
        @media print {
            body {
                margin: 0;
                background: white;
            }

            .container {
                width: 100%;
                max-width: none;
                padding: 20px;
                margin: 0;
            }

            .section-title {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <?php echo renderPrintComponents(); ?>
    <div class="container">
        <div class="logo-container">
            <img src="./image1.png" alt="Logo Esquerda" class="logo logo-left">
            <img src="./image3.png" alt="Logo Direita" class="logo logo-right">
            <div class="centered-title">
                <p>Estado do Rio Grande do Norte</p>
                <p>Prefeitura Municipal de Pau dos Ferros</p>
                <p>Secretaria de Governo – SEGOV</p>
                <p>Departamento Municipal de Trânsito – DEMUTRAN</p>
            </div>
        </div>

        <h3 class="text-center">PARECER TÉCNICO</h3>

        <?php if (!empty($dados['protocolo'])): ?>
            <div class="protocol-number">
                <strong>PROTOCOLO Nº:</strong>
                <span><?php echo htmlspecialchars($dados['protocolo']); ?></span>
            </div>
        <?php endif; ?>

        <div class="content">
            <div class="section-title">DADOS DO SOLICITANTE</div>
            <table class="data-table">
                <tr>
                    <td width="30%"><strong>Nome:</strong></td>
                    <td><?php echo htmlspecialchars($dados['nome'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>CPF/CNPJ:</strong></td>
                    <td><?php echo htmlspecialchars($dados['cpf_cnpj'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Telefone:</strong></td>
                    <td><?php echo htmlspecialchars($dados['telefone'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo htmlspecialchars($dados['email'] ?? ''); ?></td>
                </tr>
            </table>

            <div class="section-title">DADOS DA SOLICITAÇÃO</div>
            <table class="data-table">
                <tr>
                    <td width="30%"><strong>Protocolo:</strong></td>
                    <td><?php echo htmlspecialchars($dados['protocolo'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Local:</strong></td>
                    <td><?php echo htmlspecialchars($dados['local'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Evento:</strong></td>
                    <td><?php echo htmlspecialchars($dados['evento'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Ponto de Referência:</strong></td>
                    <td><?php echo htmlspecialchars($dados['ponto_referencia'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Data/Horário:</strong></td>
                    <td><?php echo htmlspecialchars($dados['data_horario'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td><strong>Data de Submissão:</strong></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($dados['data_submissao'] ?? '')); ?></td>
                </tr>
                <tr>
                    <td><strong>Situação:</strong></td>
                    <td><?php echo htmlspecialchars($dados['situacao'] ?? 'Pendente'); ?></td>
                </tr>
                <?php if ($dados['declaracao']): ?>
                    <tr>
                        <td><strong>Declaração:</strong></td>
                        <td>Aceita</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="signature-section">
            <div class="signature-line"></div>
            <p>Assinatura do Solicitante</p>
            <div class="signature-line mt-4"></div>
            <p>Assinatura do Responsável Técnico</p>
        </div>
    </div>
</body>

</html>