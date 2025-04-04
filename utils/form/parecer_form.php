<?php
// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processar os dados do formulário
    $nomeSolicitante = $_POST['nomeSolicitante'];
    $cpfCnpj = $_POST['cpfCnpj'];
    $telefone = $_POST['telefone'];
    $localEvento = $_POST['localEvento'];
    $evento = $_POST['evento'];
    $pontoReferencia = $_POST['pontoReferencia'];
    $dataHorario = $_POST['dataHorario'];

    // Formatar data para exibição
    // Mapear os meses para português
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
    $mesIngles = date('F');
    $mesPortugues = $meses[$mesIngles];
    $dataAtual = date('d') . ' de ' . $mesPortugues . ' de ' . date('Y');
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Parecer Preenchido</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Estilos Personalizados -->
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
        background-color: #e9ecef;
        padding: 10px;
        margin-top: 20px;
        font-weight: bold;
    }

    /* Compactar tabelas para economia de espaço */
    .data-table {
        width: 100%;
        margin-top: 10px;
        border-spacing: 4px;
        /* Reduz espaçamento entre as células */
    }

    .data-table td {
        padding: 3px;
        /* Reduz a quantidade de espaço em cada célula */
        vertical-align: top;
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
        text-align: left;
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
            background-color: #e9ecef !important;
        }
    }
    </style>
</head>

<body>

    <div class="container">
        <!-- Cabeçalho com logos e títulos centrais -->
        <div class="logo-container">
            <img src="image1.png" alt="Logo Esquerda" class="logo logo-left">
            <img src="image3.png" alt="Logo Direita" class="logo logo-right">
            <div class="centered-title text-center">
                <p>Estado do Rio Grande do Norte</p>
                <p>Prefeitura Municipal de Pau dos Ferros</p>
                <p>Departamento Municipal de Trânsito – DEMUTRAN</p>
            </div>
        </div>
        <h3 class="text-center mt-3">PARECER</h3>

        <!-- Conteúdo -->
        <div class="content" style="margin-top: 10%">
            <p>
                Diante levantamento feito para atender solicitação do evento a ser realizado no local infracitado, esse
                Departamento fez estudo prévio de Trânsito no referido local e é <strong>FAVORÁVEL</strong> ao
                acontecimento do mesmo, no dia e horário solicitado, conforme solicitação abaixo.
            </p>
            <p>
                Art. 95 do CTB, § 1º A obrigação de sinalizar é do responsável pela execução ou manutenção da obra ou do
                evento.
            </p>

            <!-- Dados da Solicitação -->
            <div class="section-title">DADOS DA SOLICITAÇÃO</div>
            <table class="data-table">
                <tr>
                    <td><strong>NOME DO SOLICITANTE:</strong> <?php echo $nomeSolicitante; ?></td>
                </tr>
                <tr>
                    <td><strong>CPF/CNPJ:</strong> <?php echo $cpfCnpj; ?></td>
                </tr>
                <tr>
                    <td><strong>Nº TELEFONE:</strong> <?php echo $telefone; ?></td>
                </tr>
                <tr>
                    <td><strong>LOCAL:</strong> <?php echo $localEvento; ?></td>
                </tr>
                <tr>
                    <td><strong>EVENTO:</strong> <?php echo $evento; ?></td>
                </tr>
                <tr>
                    <td><strong>PONTO DE REFERÊNCIA:</strong> <?php echo $pontoReferencia; ?></td>
                </tr>
                <tr>
                    <td><strong>DATA / HORÁRIO:</strong> <?php echo $dataHorario; ?></td>
                </tr>
            </table>

            <!-- Data e Local -->
            <div class="date-location">
                <p>Pau dos Ferros – RN, <?php echo $dataAtual; ?></p>
            </div>

            <!-- Assinatura -->
            <div class="signature" style="margin-top: 70%;">
                <div class="signature-line"></div>
                <p>José Pereira de Sousa</p>
                <p>Gerente Executivo Port. 114/2024</p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>