<?php
include '../admin/config.php';

// Verifica a conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inicializa as variáveis como strings vazias
$showModal = false;
$nomeSolicitante = $telefone = $cpfCnpj = $localEvento = $evento = $pontoReferencia = $dataHorario = "";

// Trata o envio do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeSolicitante = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $cpfCnpj = $_POST['cpf_cnpj'] ?? '';
    $localEvento = $_POST['local'] ?? '';
    $evento = $_POST['evento'] ?? '';
    $pontoReferencia = $_POST['ponto_referencia'] ?? '';
    $dataHorario = $_POST['data_horario'] ?? '';

    // Aqui você pode inserir os dados no banco de dados, se necessário
    // $sql = "INSERT INTO Parecer (nome, telefone, cpf_cnpj, local, evento, ponto_referencia, data_horario)
    //         VALUES ('$nomeSolicitante', '$telefone', '$cpfCnpj', '$localEvento', '$evento', '$pontoReferencia', '$dataHorario')";
    // $conn->query($sql);

    // Define a flag para mostrar o modal
    $showModal = true;

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Solicitação de Parecer - DEMUTRAN</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap (se necessário para outras funcionalidades) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Estilos Personalizados -->
    <style>
    /* Estilos do formulário estilizado */
    .container-form {
        max-width: 800px;
        margin: 0 auto;
    }

    .logo-container {
        position: relative;
        margin-bottom: 20px;
    }

    .logo {
        position: absolute;
        top: 10px;
        max-width: 80px;
        height: auto;
    }

    .logo-left {
        left: 10px;
    }

    .logo-right {
        right: 10px;
    }

    .centered-title p {
        margin: 2px 0;
    }

    .section-title {
        background-color: #e9ecef;
        padding: 10px;
        margin-top: 20px;
        font-weight: bold;
    }

    .data-table {
        width: 100%;
        margin-top: 10px;
        border-spacing: 4px;
    }

    .data-table td {
        padding: 3px;
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

    /* Estilo do modal */
    #modal {
        max-width: 90%;
        max-height: 90%;
        overflow-y: auto;
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
    <div id="modal-backdrop" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <!-- Modal -->
        <div id="modal" class="bg-white rounded-lg shadow-lg p-6 w-11/12 md:w-3/4 lg:w-3/4 xl:w-2/3">
            <!-- Botão de fechar -->
            <div class="flex justify-end">
                <button id="close-modal"
                    class="text-gray-500 hover:text-gray-700 text-2xl leading-none focus:outline-none">&times;</button>
            </div>
            <!-- Corpo do modal -->
            <div class="mt-4">
                <!-- Conteúdo do formulário estilizado -->
                <div class="container-form">
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
                    <div class="content">
                        <p>
                            Diante levantamento feito para atender solicitação do evento a ser realizado no local
                            infracitado, esse Departamento fez estudo prévio de Trânsito no referido local e é
                            <strong>FAVORÁVEL</strong> ao acontecimento do mesmo, no dia e horário solicitado, conforme
                            solicitação abaixo.
                        </p>
                        <p>
                            Art. 95 do CTB, § 1º A obrigação de sinalizar é do responsável pela execução ou manutenção
                            da obra ou do evento.
                        </p>

                        <!-- Dados da Solicitação -->
                        <div class="section-title">DADOS DA SOLICITAÇÃO</div>
                        <table class="data-table">
                            <tr>
                                <td><strong>NOME DO SOLICITANTE:</strong>
                                    <?php echo htmlspecialchars($nomeSolicitante, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>CPF/CNPJ:</strong>
                                    <?php echo htmlspecialchars($cpfCnpj, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nº TELEFONE:</strong>
                                    <?php echo htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>LOCAL:</strong>
                                    <?php echo htmlspecialchars($localEvento, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>EVENTO:</strong>
                                    <?php echo htmlspecialchars($evento, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>PONTO DE REFERÊNCIA:</strong>
                                    <?php echo htmlspecialchars($pontoReferencia, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>DATA / HORÁRIO:</strong>
                                    <?php echo htmlspecialchars($dataHorario, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        </table>

                        <!-- Data e Local -->
                        <div class="date-location">
                            <p>Pau dos Ferros – RN, <?php echo $dataAtual; ?></p>
                        </div>

                        <!-- Assinatura -->
                        <div class="signature">
                            <div class="signature-line"></div>
                            <p>José Pereira de Sousa</p>
                            <p>Gerente Executivo Port. 114/2024</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Rodapé do modal -->
            <div class="mt-6 text-right">
                <button id="back-button" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Voltar</button>
                <button onclick="printModalContent()"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Imprimir
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap JS (se necessário) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
    const backButton = document.getElementById('back-button');

    function closeModal() {
        modal.style.display = 'none';
        backdrop.style.display = 'none';
    }

    closeModalBtn.addEventListener('click', closeModal);
    backButton.addEventListener('click', closeModal);
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            closeModal();
        }
    });

    function printModalContent() {
        var printContents = modal.innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
        location.reload();
    }
    <?php endif; ?>
    </script>
</body>

</html>