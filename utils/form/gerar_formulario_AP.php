<?php
session_start();
require_once '../../env/config.php';
require_once '../../components/print-components.php';

// Define o órgão emissor como constante
define('ORGAO_EMISSOR', 'DEMUTRAN   21787-0');

// Função para verificar se um valor está vazio
function verificaValor($valor)
{
    return (!empty($valor) && $valor !== 'null') ? $valor : 'não informado';
}

try {
    // Verifica se está recebendo dados via POST ou GET
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Processa dados do POST
        $formData = $_POST;

        // Campos básicos
        $tipoRequerente = verificaValor($formData['tipo_requerente']);
        $nome = verificaValor($formData['nome']);
        $cpf = verificaValor($formData['cpf']);
        $endereco = verificaValor($formData['endereco']);
        $numero = verificaValor($formData['numero']);
        $complemento = verificaValor($formData['complemento']);
        $bairro = verificaValor($formData['bairro']);
        $cep = verificaValor($formData['cep']);
        $municipio = verificaValor($formData['municipio']);
        $telefone = verificaValor($formData['telefone']);
        $email = verificaValor($formData['gmail']);

        // Dados do veículo e infração
        $placa = verificaValor($formData['placa']);
        $marcaModelo = verificaValor($formData['marcaModelo']);
        $autoInfracao = verificaValor($formData['autoInfracao']);
        $identidade = verificaValor($formData['identidade']);
        $registro_cnh_infrator = verificaValor($formData['registro_cnh_infrator']);
        $cnh_numero = verificaValor($formData['cnh_numero']);
        $cnh_uf = verificaValor($formData['cnh_uf']);
    } else if (isset($_GET['id'])) {
        // Processa dados do GET (quando vem da página de detalhes)
        $id = $_GET['id'];

        // Busca os dados do formulário no banco com todos os campos necessários
        $sql = "SELECT 
            tipo_requerente, nome, cpf, endereco, numero, complemento,
            bairro, cep, municipio, telefone, gmail as email,
            placa, marcaModelo as marca_modelo, autoInfracao as auto_infracao,
            identidade, registro_cnh_infrator, cnh_numero, cnh_uf
            FROM solicitacoes_demutran 
            WHERE id = ? AND tipo_solicitacao = 'apresentacao_condutor'";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Erro ao preparar consulta: ' . $conn->error);
        }

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao executar consulta: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $dados = $result->fetch_assoc();

        if (!$dados) {
            throw new Exception('Formulário não encontrado ou tipo incorreto');
        }

        // Atribui os valores do banco às variáveis usando a função verificaValor
        $tipoRequerente = verificaValor($dados['tipo_requerente']);
        $nome = verificaValor($dados['nome']);
        $cpf = verificaValor($dados['cpf']);
        $endereco = verificaValor($dados['endereco']);
        $numero = verificaValor($dados['numero']);
        $complemento = verificaValor($dados['complemento']);
        $bairro = verificaValor($dados['bairro']);
        $cep = verificaValor($dados['cep']);
        $municipio = verificaValor($dados['municipio']);
        $telefone = verificaValor($dados['telefone']);
        $email = verificaValor($dados['email']);

        // Dados específicos da apresentação de condutor
        $placa = verificaValor($dados['placa']);
        $marcaModelo = verificaValor($dados['marca_modelo']);
        $autoInfracao = verificaValor($dados['auto_infracao']);
        $identidade = verificaValor($dados['identidade']);
        $registro_cnh_infrator = verificaValor($dados['registro_cnh_infrator']);
        $cnh_numero = verificaValor($dados['cnh_numero']);
        $cnh_uf = verificaValor($dados['cnh_uf']);
    } else {
        throw new Exception('Método não permitido');
    }

    // Formatar data atual
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
        <title>Formulário de Indicação de Condutor Infrator</title>
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
                background-color: #E3F2FD;
                /* Azul claro moderno */
                padding: 10px;
                margin-top: 20px;
                font-weight: bold;
                border-left: 4px solid #2196F3;
                /* Borda lateral azul mais escura */
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
                    background-color: #E3F2FD !important;
                }
            }

            /* Estilo para as duas assinaturas lado a lado */
            .double-signature {
                display: flex;
                justify-content: space-between;
                margin-top: 50px;
            }

            .signature-block {
                width: 45%;
                text-align: center;
            }
        </style>
    </head>

    <body>
        <?php echo renderPrintComponents(); ?>
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
                <img src="./image1.png" alt="Logo Esquerda" class="logo logo-left">
                <img src="./image3.png" alt="Logo Direita" class="logo logo-right">
                <div class="centered-title text-center">
                    <p>Estado do Rio Grande do Norte</p>
                    <p>Prefeitura Municipal de Pau dos Ferros</p>
                    <p>Secretaria de Governo – SEGOV</p>
                    <p>Departamento Municipal de Trânsito – DEMUTRAN</p>
                </div>
            </div>
            <h3 class="text-center mt-3">FORMULÁRIO DE INDICAÇÃO DE CONDUTOR INFRATOR – FICI (TRANSFERÊNCIA DE PONTUAÇÃO)
            </h3>



            <!-- Conteúdo -->
            <div class="content">
                <!-- Documentos Necessários -->
                <p><strong>O formulário deverá ser apresentado contendo os seguintes documentos:</strong></p>
                <ul>
                    <li>Formulário de Identificação datado e assinado;</li>
                    <li>Procuração, quando for o caso;</li>
                    <li>Na impossibilidade da coleta da assinatura do condutor infrator, cópia de documento que conste
                        cláusula de responsabilidade pelas infrações cometidas na condução do veículo.</li>
                </ul>

                <p>Conforme art. 5º da Resolução 845/21 do CONTRAN, a indicação do condutor infrator somente será acatada e
                    produzirá efeitos legais se o formulário de identificação do condutor estiver corretamente preenchido,
                    sem rasuras, com assinaturas originais do condutor e do proprietário do veículo.</p>

                <p>Independente da identificação do condutor, as notificações e a responsabilidade pelo pagamento da multa
                    são do proprietário do veículo, conforme § 3º do art. 282 do CTB.</p>

                <!-- Dados da Infração -->
                <div class="section-title">DADOS DA INFRAÇÃO</div>
                <table class="data-table">
                    <tr>
                        <td><strong>Auto de Infração:</strong> <?php echo $autoInfracao; ?></td>
                        <td><strong>Placa do Veículo:</strong> <?php echo $placa; ?></td>
                        <td><strong>Órgão Autuador:</strong> <?php echo ORGAO_EMISSOR; ?></td>
                    </tr>
                </table>

                <!-- Dados do Condutor Infrator -->
                <div class="section-title">DADOS DO CONDUTOR INFRATOR</div>
                <table class="data-table">
                    <tr>
                        <td><strong>Nome:</strong> <?php echo $nome; ?></td>
                        <td><strong>Identidade/Órgão emissor:</strong> <?php echo $identidade; ?></td>
                        <td><strong>CPF:</strong> <?php echo $cpf; ?></td>
                    </tr>
                    <tr>
                        <td><strong>CNH:</strong> <?php echo $cnh_numero; ?></td>
                        <td><strong>UF:</strong> <?php echo $cnh_uf; ?></td>
                        <td><strong>Registro CNH:</strong> <?php echo $registro_cnh_infrator; ?></td>
                    </tr>
                    <tr>
                        <td colspan="3"><strong>Residente à:</strong> <?php echo $endereco; ?>, Nº <?php echo $numero; ?>
                            <?php if ($complemento) echo ", Compl.: $complemento"; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Bairro:</strong> <?php echo $bairro; ?></td>
                        <td><strong>CEP:</strong> <?php echo $cep; ?></td>
                        <td><strong>Município:</strong> <?php echo $municipio; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Telefone:</strong> <?php echo $telefone; ?></td>
                        <td><strong>E-mail:</strong> <?php echo $email; ?></td>
                    </tr>
                </table>

                <p>Declaro, nos termos do art. 4º da Lei 9.784/99, serem verdadeiras as informações aqui prestadas, sobre as
                    quais assumo todas as responsabilidades, sob pena de incorrer nas sanções previstas na legislação penal.
                </p>

                <div class="date-location">
                    <p>Pau dos Ferros/RN, <?php echo $dataAtual; ?></p>
                </div>

                <div class="double-signature">
                    <div class="signature-block">
                        <div class="signature-line"></div>
                        <p>Assinatura do Condutor Infrator<br>(Pessoa que receberá a pontuação)</p>
                    </div>
                    <div class="signature-block">
                        <div class="signature-line"></div>
                        <p>Assinatura do Proprietário / Principal Condutor<br>(Pessoa que está transferindo a pontuação)</p>
                    </div>
                </div>
            </div>

        </div>
        </div>

        <!-- Scripts -->
        <!-- jQuery and Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.1/js/bootstrap.min.js"></script>

    </body>

    </html>

<?php
} catch (Exception $e) {
    error_log('Erro ao gerar formulário: ' . $e->getMessage());
    die('Erro ao gerar o formulário. Por favor, verifique todos os campos obrigatórios e tente novamente.');
}
?>