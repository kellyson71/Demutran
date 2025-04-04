<?php
session_start();
require_once '../../env/config.php';
require_once '../../components/print-components.php';

// Obter dados do formulário via POST ou GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['form_data'] = $_POST;
    $formData = $_POST;
} else {
    // Verificar se temos ID na URL
    $id = $_GET['id'] ?? '';

    if ($id) {
        // Consultar banco de dados
        $sql = "SELECT * FROM sac WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $formData = $result->fetch_assoc();

        if (!$formData) {
            die('Formulário não encontrado');
        }
    } else if (isset($_SESSION['form_data'])) {
        $formData = $_SESSION['form_data'];
    } else {
        die('Dados do formulário não encontrados');
    }
}

// Extrair dados do formulário
$nome = $formData['nome'] ?? '';
$email = $formData['email'] ?? '';
$telefone = $formData['telefone'] ?? '';
$tipo_contato = ucfirst($formData['tipo_contato'] ?? 'Contato');
$assunto = $formData['assunto'] ?? '';
$mensagem = $formData['mensagem'] ?? '';
$data_submissao = isset($formData['data_submissao']) ? new DateTime($formData['data_submissao']) : new DateTime();
$situacao = $formData['situacao'] ?? 'Pendente';

// Formatar data atual para português
$meses = array(
    'January' => 'janeiro',
    'February' => 'fevereiro',
    'March' => 'março',
    'April' => 'abril',
    'May' => 'maio',
    'June' => 'junho',
    'July' => 'julho',
    'August' => 'agosto',
    'September' => 'setembro',
    'October' => 'outubro',
    'November' => 'novembro',
    'December' => 'dezembro'
);
$dataFormatada = $data_submissao->format('d') . ' de ' . $meses[$data_submissao->format('F')] . ' de ' . $data_submissao->format('Y');

// Adicionar formatação para o status
function getStatusColor($situacao)
{
    return match ($situacao) {
        'Concluído' => '#4CAF50',
        'Em andamento' => '#2196F3',
        default => '#FFC107'
    };
}

$statusColor = getStatusColor($situacao);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Formulário SAC</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            margin-top: 20px;
        }

        .container {
            max-width: 800px;
        }

        .header {
            text-align: center;
        }

        /* Alinhamento e espaçamento do cabeçalho */
        /* Ajuste para manter as logos fixas no topo */
        .logo-container {
            position: relative;
        }

        .logo {
            position: absolute;
            top: 10px;
            /* Ajuste a distância do topo conforme necessário */
            max-width: 80px;
            height: auto;
        }

        .logo-left {
            left: 10px;
            /* Ajuste a distância da borda esquerda conforme necessário */
        }

        .logo-right {
            right: 10px;
            /* Ajuste a distância da borda direita conforme necessário */
        }

        .centered-title p {
            margin: 2px 0;
            /* Reduzir espaçamento entre linhas do título */
        }

        .row.align-items-center p {
            margin: 0;
            /* Reduzir o espaçamento entre os títulos */
        }

        .title {
            text-align: center;
            margin-top: 20px;
        }

        h3 {
            font-weight: bold;
            margin-top: 15px;
        }

        .content {
            margin-top: 20px;
        }

        .section-title {
            background-color: #E3F2FD;
            padding: 12px 15px;
            margin: 25px 0 15px 0;
            font-weight: bold;
            border-left: 4px solid #2196F3;
            border-radius: 0 4px 4px 0;
            color: #1565C0;
        }

        /* Compactar tabelas para economia de espaço */
        .data-table {
            width: 100%;
            margin: 15px 0;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .data-table td {
            line-height: 1.5;
        }

        .data-row {
            background-color: #f8f9fa;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .data-row:hover {
            background-color: #f1f3f5;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .message-box {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .protocol-number {
            background-color: #E3F2FD;
            color: #1565C0;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            display: inline-block;
            margin-top: 20px;
        }

        .header-info {
            text-align: center;
            padding: 20px 0 30px 0;
        }

        .header-info h3 {
            color: #1565C0;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .signature {
            margin-top: 50px;
            text-align: center;
        }

        .signature-line {
            width: 50%;
            border-top: 1px solid #000;
            margin: 0 auto;
        }

        .date-location {
            text-align: right;
            margin-top: 20px;
        }

        /* Estilo da linha de assinatura */
        .signature-line {
            width: 60%;
            border-top: 1px solid #000;
            margin: 0 auto 10px;
            /* Reduz o espaçamento */
        }

        /* Estilização para impressão */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .section-title {
                -webkit-print-color-adjust: exact;
                background-color: #E3F2FD !important;
                border-left-color: #2196F3 !important;
            }

            .status-badge {
                -webkit-print-color-adjust: exact;
                box-shadow: none !important;
            }
        }

        /* Adicione estilos específicos para o formulário SAC */
        .sac-header {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }

        .sac-content {
            background-color: #fff;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <?php echo renderPrintComponents(); ?>

    <div class="container">
        <!-- Cabeçalho com logos -->
        <div class="logo-container mb-4">
            <img src="./image1.png" alt="Logo Esquerda" class="logo logo-left">
            <img src="./image3.png" alt="Logo Direita" class="logo logo-right">
            <div class="centered-title text-center">
                <p>Estado do Rio Grande do Norte</p>
                <p>Prefeitura Municipal de Pau dos Ferros</p>
                <p>Departamento Municipal de Trânsito – DEMUTRAN</p>
            </div>
        </div>

        <div class="sac-content">
            <div class="header-info">
                <h3>FORMULÁRIO DE <?php echo strtoupper($tipo_contato); ?></h3>
                <div class="protocol-number">
                    Protocolo: #<?php echo str_pad($formData['id'] ?? '0', 6, '0', STR_PAD_LEFT); ?>
                </div>

</html>
</div>

<!-- Informações do Solicitante -->
<div class="section-title">DADOS DO SOLICITANTE</div>
<div class="data-row">
    <table class="data-table">
        <tr>
            <td width="50%"><strong>Nome:</strong> <?php echo $nome; ?></td>
            <td><strong>Email:</strong> <?php echo $email; ?></td>
        </tr>
        <tr>
            <td><strong>Telefone:</strong> <?php echo $telefone; ?></td>
            <td><strong>Data da Solicitação:</strong> <?php echo $dataFormatada; ?></td>
        </tr>
    </table>
</div>

<!-- Detalhes da Solicitação -->
<div class="section-title">DETALHES DA SOLICITAÇÃO</div>
<div class="data-row">
    <table class="data-table">
        <!-- <tr>
            <td width="50%"><strong>Tipo de Contato:</strong> <?php echo $tipo_contato; ?></td>
            <td>
                <strong>Status:</strong>
                <span class="status-badge" style="background-color: <?php echo $statusColor; ?>; color: white;">
                    <?php echo $situacao; ?>
                </span>
            </td>
        </tr> -->
        <tr>
            <td colspan="2"><strong>Assunto:</strong> <?php echo $assunto; ?></td>
        </tr>
    </table>
</div>

<!-- Mensagem -->
<div class="message-box mt-4">
    <strong>Mensagem:</strong><br>
    <p class="mt-2" style="white-space: pre-line;"><?php echo $mensagem; ?></p>
</div>

<!-- Data e Local -->
<div class="date-location mt-5">
    <p>Pau dos Ferros/RN, <?php echo date('d') . ' de ' . $meses[date('F')] . ' de ' . date('Y'); ?></p>
</div>

<!-- Indicação de solicitação online -->
<div class="text-center mt-4">
    <p><em>Solicitação registrada através do sistema oficial SAC - DEMUTRAN</em></p>
</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>