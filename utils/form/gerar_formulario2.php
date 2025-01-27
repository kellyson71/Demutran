<?php
if (!isset($dados)) {
    die('Acesso direto não permitido');
}
require_once '../../components/print-components.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Solicitação de Cartão PCD</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            margin: 20px 0;
            background: white;
        }

        .container {
            max-width: 800px;
            background: white;
            padding: 20px;
            margin: 0 auto;
        }

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

        .centered-title {
            text-align: center;
            padding: 0 100px;
            margin-top: 20px;
        }

        .centered-title p {
            margin: 2px 0;
            line-height: 1.4;
        }

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

        .signature-section {
            margin-top: 50px;
            text-align: center;
        }

        .signature-line {
            width: 60%;
            border-top: 1px solid #000;
            margin: 0 auto 5px;
        }

        @media print {
            body {
                margin: 0;
                background: white;
            }

            .container {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 15px;
            }

            .section-title {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <?php echo renderPrintComponents(); ?>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="logo-container">
            <img src="image1.png" alt="Logo Esquerda" class="logo logo-left">
            <img src="image3.png" alt="Logo Direita" class="logo logo-right">
            <div class="centered-title">
                <p>Estado do Rio Grande do Norte</p>
                <p>Prefeitura Municipal de Pau dos Ferros</p>
                <p>Secretaria de Governo – SEGOV</p>
                <p>Departamento Municipal de Trânsito – DEMUTRAN</p>
            </div>
        </div>

        <h3 class="text-center mb-4">
            <?php
            $titulo = "SOLICITAÇÃO DE CARTÃO DE ESTACIONAMENTO ";
            $titulo .= strtoupper($dados['tipo_solicitacao'] ?? 'PCD');
            echo $titulo;
            ?>
        </h3>

        <!-- Número do Cartão -->
        <div class="text-right mb-4">
            <strong>Nº do Cartão:</strong> <?php echo htmlspecialchars($dados['n_cartao'] ?? 'Não atribuído'); ?>
        </div>

        <!-- Dados do Solicitante -->
        <div class="section-title">DADOS DO SOLICITANTE</div>
        <table class="data-table">
            <tr>
                <td width="30%"><strong>Nome:</strong></td>
                <td><?php echo htmlspecialchars($dados['nome'] ?? ''); ?></td>
            </tr>
            <tr>
                <td><strong>CPF:</strong></td>
                <td><?php echo htmlspecialchars($dados['cpf'] ?? ''); ?></td>
            </tr>
            <tr>
                <td><strong>Data de Nascimento:</strong></td>
                <td><?php echo htmlspecialchars($dados['data_nascimento'] ?? ''); ?></td>
            </tr>
            <tr>
                <td><strong>Nº Documento de Identidade:</strong></td>
                <td><?php echo htmlspecialchars($dados['doc_identidade_num'] ?? ''); ?></td>
            </tr>
            <tr>
                <td><strong>Endereço:</strong></td>
                <td><?php echo htmlspecialchars($dados['endereco'] ?? ''); ?></td>
            </tr>
            <tr>
                <td><strong>Telefone:</strong></td>
                <td><?php echo htmlspecialchars($dados['telefone'] ?? ''); ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><?php echo htmlspecialchars($dados['email'] ?? ''); ?></td>
            </tr>

            <tr>
                <td><strong>Tipo de Solicitação:</strong></td>
                <td><?php echo htmlspecialchars($dados['tipo_solicitacao'] ?? ''); ?></td>
            </tr>
            <tr>
                <td><strong>Data de Submissão:</strong></td>
                <td><?php echo htmlspecialchars($dados['data_submissao'] ?? ''); ?></td>
            </tr>
        </table>

        <!-- Dados do Responsável Legal (se existir) -->
        <?php if (!empty($dados['cpf_representante']) || !empty($dados['nome_representante'])): ?>
            <div class="section-title mt-4">DADOS DO RESPONSÁVEL</div>
            <table class="data-table">
                <tr>
                    <td width="30%"><strong>Nome do Responsável:</strong></td>
                    <td><?php echo htmlspecialchars($dados['nome_representante'] ?? 'Não informado'); ?></td>
                </tr>
                <tr>
                    <td><strong>CPF do Responsável:</strong></td>
                    <td><?php echo htmlspecialchars($dados['cpf_representante'] ?? 'Não informado'); ?></td>
                </tr>
                <tr>
                    <td><strong>Endereço do Responsável:</strong></td>
                    <td><?php echo htmlspecialchars($dados['endereco_representante'] ?? 'Não informado'); ?></td>
                </tr>
                <tr>
                    <td><strong>Email do Responsável:</strong></td>
                    <td><?php echo htmlspecialchars($dados['email_representante'] ?? 'Não informado'); ?></td>
                </tr>
                <tr>
                    <td><strong>Telefone do Responsável:</strong></td>
                    <td><?php echo htmlspecialchars($dados['telefone_representante'] ?? 'Não informado'); ?></td>
                </tr>
            </table>
        <?php endif; ?>

        <!-- Informações do Cartão -->
        <div class="section-title mt-4">INFORMAÇÕES DO CARTÃO</div>
        <table class="data-table">
            <tr>
                <td width="30%"><strong>Solicitante:</strong></td>
                <td><?php echo htmlspecialchars($dados['solicitante'] ?? ''); ?></td>
            </tr>
            <tr>
                <td><strong>Emissão do Cartão:</strong></td>
                <td><?php echo htmlspecialchars($dados['emissao_cartao'] ?? ''); ?></td>
            </tr>
        </table>

        <!-- Data e Assinaturas -->
        <div class="signature-section">
            <p class="mb-4">Pau dos Ferros/RN, <?php echo date('d/m/Y'); ?></p>
            <div class="signature-line"></div>
            <p>Assinatura do Solicitante</p>
            <?php if ($dados['representante_legal'] === 'sim'): ?>
                <div class="signature-line mt-4"></div>
                <p>Assinatura do Representante Legal</p>
            <?php endif; ?>
            <div class="signature-line mt-4"></div>
            <p>Assinatura do Responsável - DEMUTRAN</p>
        </div>
    </div>

    <?php if (isset($_GET['print']) && $_GET['print'] === 'true'): ?>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    <?php endif; ?>
</body>

</html>