<?php
include '../admin/config.php';

// Verifica a conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inicializa as variáveis como strings vazias
$showModal = false;
$nomeSolicitante = $telefone = $cpfCnpj = $localEvento = $evento = $pontoReferencia = $dataHorario = "";
$protocolo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeSolicitante = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $cpfCnpj = $_POST['cpf_cnpj'] ?? '';
    $localEvento = $_POST['local'] ?? '';
    $evento = $_POST['evento'] ?? '';
    $pontoReferencia = $_POST['ponto_referencia'] ?? '';
    $dataHorario = $_POST['data_horario'] ?? '';

    // Gera um número de protocolo aleatório de 5 dígitos
    $protocolo = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

    // Aqui você pode inserir os dados no banco de dados, incluindo o protocolo
    // $sql = "INSERT INTO Parecer (protocolo, nome, telefone, cpf_cnpj, local, evento, ponto_referencia, data_horario)
    //         VALUES ('$protocolo', '$nomeSolicitante', '$telefone', '$cpfCnpj', '$localEvento', '$evento', '$pontoReferencia', '$dataHorario')";
    // $conn->query($sql);

    // Define a flag para mostrar o modal
    $showModal = true;

    // Formatar data para exibição
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Solicitação de Parecer - DEMUTRAN</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Estilos Personalizados -->
    <style>
    /* Estilos personalizados */
    .print-button {
        display: none;
    }

    /* Estilos para o modal */
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.7);
    }

    .modal-content {
        max-width: 600px;
    }

    /* Estilos para impressão */
    @media print {
        body * {
            visibility: hidden;
        }

        #print-section,
        #print-section * {
            visibility: visible;
        }

        #print-section {
            position: absolute;
            left: 0;
            top: 0;
        }

        /* Remover fundos e cores */
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background: white;
        }

        .no-print {
            display: none;
        }
    }
    </style>
</head>

<body class="min-h-screen bg-gray-100">
    <!-- Topbar -->
    <header class="bg-green-600 text-white shadow-md w-full fixed top-0 left-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Logo -->
            <div class="text-lg font-semibold">
                <a href="../" class="hover:text-green-300 text-white no-underline">DEMUTRAN</a>
            </div>

            <!-- Links -->
            <nav class="hidden md:flex space-x-6">
                <a href="../PCD/index.html" class="hover:text-green-300 text-white no-underline">Cartão Vaga
                    Especial</a>
                <a href="../Defesa/index.html" class="hover:text-green-300 text-white no-underline">Defesa
                    Prévia/JARI</a>
                <a href="../DAT/index.php" class="hover:text-green-300 text-white no-underline">DAT</a>
                <a href="../contato/index.html" class="hover:text-green-300 text-white no-underline">Contato</a>
            </nav>

            <!-- Botão do menu mobile -->
            <div class="md:hidden">
                <button id="menu-btn" class="text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Menu Mobile -->
        <div id="mobile-menu" class="hidden md:hidden bg-green-600">
            <a href="../PCD/index.html" class="block px-4 py-2 text-white hover:bg-green-500 no-underline">Cartão Vaga
                Especial</a>
            <a href="../Defesa/index.html" class="block px-4 py-2 text-white hover:bg-green-500 no-underline">Defesa
                Prévia/JARI</a>
            <a href="../DAT/index.php" class="block px-4 py-2 text-white hover:bg-green-500 no-underline">DAT</a>
            <a href="../contato/index.html"
                class="block px-4 py-2 text-white hover:bg-green-500 no-underline">Contato</a>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <div class="pt-24 flex justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                SOLICITAÇÃO DE PARECER AO DEPARTAMENTO MUNICIPAL DE TRÂNSITO – DEMUTRAN/PAU DOS FERROS
            </h2>
            <p class="text-gray-600 mb-6">
                <strong>Preencha todos os dados para podermos dar continuidade à sua solicitação.</strong>
            </p>

            <form method="post">
                <!-- Nome -->
                <div class="mb-4">
                    <label for="nome" class="block text-gray-700 mb-2">NOME DO SOLICITANTE:</label>
                    <input type="text" id="nome" name="nome" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        value="<?php echo htmlspecialchars($nomeSolicitante ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Telefone -->
                <div class="mb-4">
                    <label for="telefone" class="block text-gray-700 mb-2">Nº TELEFONE:</label>
                    <input type="tel" id="telefone" name="telefone" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        value="<?php echo htmlspecialchars($telefone ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- CPF/CNPJ -->
                <div class="mb-4">
                    <label for="cpf_cnpj" class="block text-gray-700 mb-2">CPF/CNPJ:</label>
                    <input type="text" id="cpf_cnpj" name="cpf_cnpj" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        value="<?php echo htmlspecialchars($cpfCnpj ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Local -->
                <div class="mb-4">
                    <label for="local" class="block text-gray-700 mb-2">LOCAL:</label>
                    <input type="text" id="local" name="local" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        value="<?php echo htmlspecialchars($localEvento ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Evento -->
                <div class="mb-4">
                    <label for="evento" class="block text-gray-700 mb-2">Evento:</label>
                    <input type="text" id="evento" name="evento" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        value="<?php echo htmlspecialchars($evento ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Ponto de Referência -->
                <div class="mb-4">
                    <label for="ponto_referencia" class="block text-gray-700 mb-2">Ponto de Referência:</label>
                    <input type="text" id="ponto_referencia" name="ponto_referencia" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        value="<?php echo htmlspecialchars($pontoReferencia ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Data/Horário -->
                <div class="mb-4">
                    <label for="data_horario" class="block text-gray-700 mb-2">Data/Horário:</label>
                    <input type="text" id="data_horario" name="data_horario" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        value="<?php echo htmlspecialchars($dataHorario ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Botão de Enviar -->
                <button type="submit"
                    class="bg-green-600 text-white px-4 py-2 rounded mt-4 hover:bg-green-700">Enviar</button>
            </form>
        </div>
    </div>

    <!-- Modal e Backdrop -->
    <?php if ($showModal): ?>
    <!-- Fundo do modal -->
    <div id="modal-backdrop" class="modal-overlay fixed inset-0 flex items-center justify-center">
        <!-- Modal -->
        <div id="modal" class="modal-content bg-white rounded-lg shadow-lg p-6">
            <!-- Corpo do modal -->
            <div class="mt-4 text-center">
                <h3 class="text-2xl font-semibold mb-4 text-gray-800">Solicitação Enviada com Sucesso</h3>
                <p class="mb-4 text-gray-700">Protocolo de Atendimento: <strong><?php echo $protocolo; ?></strong></p>
                <p class="mb-4 text-gray-700">Sua solicitação foi recebida com sucesso. Anote o número do protocolo para
                    futuras consultas.</p>
                <p class="mb-4 text-gray-700">Em breve, entraremos em contato com informações sobre o andamento do seu
                    pedido.</p>
            </div>
            <!-- Rodapé do modal -->
            <div class="mt-6 text-center">
                <button id="print-button" onclick="openPrintWindow()"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Imprimir Comprovante</button>
                <button id="close-modal"
                    class="bg-gray-500 text-white px-4 py-2 rounded ml-2 hover:bg-gray-600">Fechar</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script>
    // Script para alternar o menu mobile
    const menuBtn = document.getElementById("menu-btn");
    const mobileMenu = document.getElementById("mobile-menu");

    menuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
    });

    // Script para controlar o modal
    <?php if ($showModal): ?>
    const modal = document.getElementById('modal');
    const backdrop = document.getElementById('modal-backdrop');
    const closeModalBtn = document.getElementById('close-modal');

    function closeModal() {
        modal.style.display = 'none';
        backdrop.style.display = 'none';
    }

    closeModalBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            closeModal();
        }
    });

    // Função para abrir a janela de impressão
    function openPrintWindow() {
        const printWindow = window.open('', '_blank');
        const content = `
   
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
            <img src="arquivo/image1.png" alt="Logo Esquerda" class="logo logo-left">
            <img src="arquivo/image3.png" alt="Logo Direita" class="logo logo-right">
            <div class="centered-title text-center">
                <p>Estado do Rio Grande do Norte</p>
                <p>Prefeitura Municipal de Pau dos Ferros</p>
                <p>Secretaria de Governo – SEGOV</p>
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

</body>

</html>
`;
        printWindow.document.open();
        printWindow.document.write(content);
        printWindow.document.close();

        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();

            // Fechar a janela automaticamente após 5 segundos
            setTimeout(() => {
                printWindow.close();
            }, 2000); // 5000 milissegundos = 5 segundos
        };
    }
    <?php endif; ?>
    </script>
</body>

</html>