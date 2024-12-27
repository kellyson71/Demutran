<?php
session_start();
require_once '../../env/config.php';

// Obter dados do formulário via POST ou GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['form_data'] = $_POST;
    $formData = $_POST;
} else {
    // Verificar se temos ID e tipo na URL
    $id = $_GET['id'] ?? '';
    $tipo = $_GET['tipo_solicitacao'] ?? '';
    
    if ($id && $tipo) {
        // Consultar banco de dados
        $sql = "SELECT * FROM solicitacoes_demutran WHERE id = ?";
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

// Formatar o texto do tipo de solicitação para exibição
$tipoRequerente = $formData['tipo_solicitacao'] ?? '';
switch($tipoRequerente) {
    case 'defesa_previa':
        $tipoRequerente = 'Defesa Prévia';
        break;
    case 'jari':
        $tipoRequerente = 'JARI';
        break;
    default:
        $tipoRequerente = 'Não informado';
}

// Extrair dados do formulário
$nome = $formData['nome'] ?? '';
$cpf = $formData['cpf'] ?? '';
$endereco = $formData['endereco'] ?? '';
$numero = $formData['numero'] ?? '';
$complemento = $formData['complemento'] ?? '';
$bairro = $formData['bairro'] ?? '';
$cep = $formData['cep'] ?? '';
$municipio = $formData['municipio'] ?? '';
$telefone = $formData['telefone'] ?? '';
$placa = $formData['placa'] ?? '';
$marcaModelo = $formData['marcaModelo'] ?? '';
$cor = $formData['cor'] ?? '';
$especie = $formData['especie'] ?? '';
$categoria = $formData['categoria'] ?? '';
$ano = $formData['ano'] ?? '';
$autoInfracao = $formData['autoInfracao'] ?? '';
$dataInfracao = $formData['dataInfracao'] ?? '';
$horaInfracao = $formData['horaInfracao'] ?? '';
$localInfracao = $formData['localInfracao'] ?? '';
$enquadramento = $formData['enquadramento'] ?? '';
$defesa = $formData['defesa'] ?? '';

// Configurações baseadas no tipo de solicitação
$tipoSolicitacao = $formData['tipo_solicitacao'] ?? '';
$configs = [
    'titulo' => '',
    'bg_color' => '',
    'border_color' => '',
    'destinatario' => '',
    'texto_acao' => ''
];

switch($tipoSolicitacao) {
    case 'defesa_previa':
        $configs = [
            'titulo' => 'REQUERIMENTO PARA APRESENTAÇÃO DE DEFESA PRÉVIA',
            'bg_color' => '#E3F2FD',
            'border_color' => '#2196F3',
            'destinatario' => 'Ilustríssimo Senhor Gerente Executivo do Departamento Municipal de Trânsito – DEMUTRAN de Pau dos Ferros/RN',
            'texto_acao' => 'Vem interpor Defesa Prévia'
        ];
        break;
    case 'jari':
        $configs = [
            'titulo' => 'REQUERIMENTO PARA APRESENTAÇÃO DE RECURSO À JARI',
            'bg_color' => '#FFF3E0',
            'border_color' => '#FF9800',
            'destinatario' => 'Ilustríssimo Senhor Presidente da Junta Administrativa de Recursos de Infrações - JARI',
            'texto_acao' => 'Vem interpor Recurso'
        ];
        break;
    default:
        die('Tipo de solicitação inválido');
}

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
$mesIngles = date('F');
$mesPortugues = $meses[$mesIngles];
$dataAtual = date('d') . ' de ' . $mesPortugues . ' de ' . date('Y');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Formulário Preenchido</title>
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
        background-color: <?php echo $configs['bg_color'];
        ?>;
        padding: 10px;
        margin-top: 20px;
        font-weight: bold;
        border-left: 4px solid <?php echo $configs['border_color'];
        ?>;
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
            background-color: <?php echo $configs['bg_color'];
            ?> !important;
            border-left-color: <?php echo $configs['border_color'];
            ?> !important;
        }
    }
    </style>
</head>

<body>

    <div class="container">
        <!-- Cabeçalho com logos -->
        <!-- <div class="row header">
            <div class="col-md-6">
                <img src="logo_esquerda.png" alt="Logo Esquerda" class="logo">
            </div>
            <div class="col-md-6 text-right">
                <img src="logo_direita.png" alt="Logo Direita" class="logo">
            </div>
        </div> -->

        <!-- Títulos -->
        <!-- Cabeçalho com logos e títulos centrais -->
        <div class="logo-container">
            <img src="image1.png" alt="Logo Esquerda" class="logo logo-left">
            <img src="image3.png" alt="Logo Direita" class="logo logo-right">
            <div class="centered-title text-center">
                <p>Estado do Rio Grande do Norte</p>
                <p>Prefeitura Municipal de Pau dos Ferros</p>
                <p>Secretaria de Governo – SEGOV</p>
                <p>Departamento Municipal de Trânsito – DEMUTRAN</p>
            </div>
        </div>
        <h3 class="text-center mt-3"><?php echo $configs['titulo']; ?></h3>



        <!-- Conteúdo -->
        <div class="content">
            <!-- Documentos Necessários -->
            <p><strong>Documentos necessários para apresentação de Defesa Prévia:</strong></p>
            <ul>
                <li>Requerimento da Defesa datada e assinada;</li>
                <li>Cópia da CNH ou outro documento de identificação que comprove a assinatura que consta no
                    requerimento do Recurso. Quando o requerente for representado, apresentar também a cópia do
                    documento de identificação do representante legal. E, quando pessoa jurídica, apresentar também ato
                    constitutivo da empresa;</li>
                <li>Cópia do CRLV;</li>
                <li>Cópia da Notificação da Penalidade;</li>
                <li>Procuração, quando for o caso;</li>
                <li>Outros documentos que considerar necessários para apoiar as alegações da Defesa.</li>
            </ul>
            <p>
                A Defesa prévia deverá ser preenchida, assinada, e entregue junto com os demais documentos acima
                relacionados, ou enviada pelos Correios para o endereço:
            </p>
            <p>
                Av. Dom Pedro II nº 1121, Centro – Pau dos Ferros/RN CEP: 59900 - 000
            </p>

            <!-- Saudação -->
            <p><?php echo $configs['destinatario']; ?></p>

            <!-- Dados do Requerente -->
            <!-- Dados do Requerente -->
            <div class="section-title">DADOS DO REQUERENTE</div>
            <table class="data-table">
                <tr>
                    <td><strong>Tipo de Requerente:</strong> <?php echo $tipoRequerente; ?></td>
                    <td><strong>Nome:</strong> <?php echo $nome; ?></td>
                    <td><strong>CPF:</strong> <?php echo $cpf; ?></td>
                </tr>
                <tr>
                    <td colspan="3"><strong>Residente à:</strong> <?php echo $endereco; ?>, Nº <?php echo $numero; ?>
                        <?php if ($complemento != '') { echo 'Compl.: ' . $complemento; } ?>, Bairro:
                        <?php echo $bairro; ?></td>
                </tr>
                <tr>
                    <td><strong>CEP:</strong> <?php echo $cep; ?></td>
                    <td><strong>Município:</strong> <?php echo $municipio; ?></td>
                    <td><strong>Telefone:</strong> <?php echo $telefone; ?></td>
                </tr>
            </table>


            <!-- Dados do Veículo -->
            <div class="section-title">DADOS DO VEÍCULO</div>
            <table class="data-table">
                <tr>
                    <td><strong>Placa:</strong> <?php echo $placa; ?></td>
                    <td><strong>Marca/Modelo:</strong> <?php echo $marcaModelo; ?></td>
                    <td><strong>Cor:</strong> <?php echo $cor; ?></td>
                </tr>
                <tr>
                    <td><strong>Espécie:</strong> <?php echo $especie; ?></td>
                    <td><strong>Categoria:</strong> <?php echo $categoria; ?></td>
                    <td><strong>Ano:</strong> <?php echo $ano; ?></td>
                </tr>
            </table>


            <!-- Dados da Infração -->
            <div class="section-title">DADOS DA INFRAÇÃO</div>
            <table class="data-table">
                <tr>
                    <td><strong>Auto de Infração nº:</strong> <?php echo $autoInfracao; ?></td>
                    <td><strong>Data da Infração:</strong> <?php echo date('d/m/Y', strtotime($dataInfracao)); ?></td>
                    <td><strong>Hora da Infração:</strong> <?php echo $horaInfracao; ?></td>
                </tr>
                <tr>
                    <td colspan="3"><strong>Local da Infração:</strong> <?php echo $localInfracao; ?></td>
                </tr>
                <tr>
                    <td colspan="3"><strong>Correspondente ao enquadramento:</strong> <?php echo $enquadramento; ?></td>
                </tr>
            </table>

            <p>
                Do Código de Trânsito Brasileiro, ou seja Lei nº 9.503 de 23 de setembro de 1997 - Art. 203 V.
            </p>

            <!-- Dos Fatos -->
            <div class="section-title">DOS FATOS</div>
            <p>
                <?php echo $configs['texto_acao']; ?>, solicitando:
            </p>
            <p>
                <?php echo nl2br($defesa); ?>
            </p>

            <!-- Declaração -->
            <p>
                Declaro, nos termos do art. 4º da Lei 9.784/99, serem verdadeiras as informações aqui prestadas, sobre
                as quais assumo todas as responsabilidades, sob pena de incorrer nas sanções previstas na legislação
                penal.
            </p>

            <!-- Data e Local -->
            <div class="date-location">
                <p>Pau dos Ferros/RN, <?php echo $dataAtual; ?></p>
            </div>
            <!-- Indicação de solicitação online -->
            <div class="text-center" style="font-size: 0.9em; color: #555; margin-top: 20px;">
                <p><em>Solicitação realizada online através do sistema oficial de defesa prévia</em></p>
            </div>

            <!-- Assinatura -->
            <div class="signature">
                <div class="signature-line"></div>
                <p>Assinatura do Requerente</p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>