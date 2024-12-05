<?php
include '../env/config.php';
session_start();

// Pegar mensagens da sessão se existirem
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;

// Limpar mensagens da sessão
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Solicitação de Parecer - DEMUTRAN</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inclua o Tailwind CSS e o plugin de data/hora -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    <!-- Inclua o Bootstrap para modais, se necessário -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Inclua o Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="icon" href="../icon.png" type="image/png">
</head>

<body class="min-h-screen bg-gray-100">
    <!-- Topbar -->
    <header class="bg-green-600 text-white shadow-md w-full fixed top-0 left-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Logo -->
            <div class="text-lg font-semibold flex items-center">
                <img src="../assets/icon.png" alt="DEMUTRAN" class="h-8 w-8 mr-2">
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
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                <p><strong>ATENÇÃO:</strong></p>
                <ul class="list-disc ml-6">
                    <li>A solicitação deverá ser feita no mínimo 08 dias antes do evento</li>
                    <li>O parecer será entregue presencialmente</li>
                </ul>
            </div>
            <p class="text-gray-600 mb-6">
                <strong>Preencha todos os dados para podermos dar continuidade à sua solicitação.</strong>
            </p>

            <?php if (isset($successMessage)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $successMessage; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $errorMessage; ?>
            </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" action="processa_formulario.php">
                <!-- Campos básicos -->
                <div class="mb-4">
                    <label for="nome" class="block text-gray-700 mb-2">NOME DO SOLICITANTE:</label>
                    <input type="text" id="nome" name="nome_solicitante" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Exemplo: João da Silva">
                </div>

                <!-- Telefone -->
                <div class="mb-4">
                    <label for="telefone" class="block text-gray-700 mb-2">Nº TELEFONE:</label>
                    <input type="tel" id="telefone" name="telefone" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Exemplo: (84) 99999-9999">
                </div>

                <!-- CPF/CNPJ -->
                <div class="mb-4">
                    <label for="cpf_cnpj" class="block text-gray-700 mb-2">CPF/CNPJ:</label>
                    <input type="text" id="cpf_cnpj" name="cpf_cnpj" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Exemplo: 123.456.789-00">
                </div>

                <!-- Campo para E-MAIL -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">E-MAIL:</label>
                    <input type="email" id="email" name="email" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Exemplo: seuemail@exemplo.com">
                </div>

                <!-- Local -->
                <div class="mb-4">
                    <label for="local" class="block text-gray-700 mb-2">LOCAL:</label>
                    <input type="text" id="local" name="local" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Exemplo: Rua das Flores, 123">
                </div>

                <!-- Evento -->
                <div class="mb-4">
                    <label for="evento" class="block text-gray-700 mb-2">Evento:</label>
                    <input type="text" id="evento" name="evento" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Exemplo: Festa de Aniversário">
                </div>

                <!-- Ponto de Referência -->
                <div class="mb-4">
                    <label for="ponto_referencia" class="block text-gray-700 mb-2">Ponto de Referência:</label>
                    <input type="text" id="ponto_referencia" name="ponto_referencia" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Exemplo: Próximo ao Mercado Central">
                </div>

                <!-- Data/Horário -->
                <div class="mb-4">
                    <label for="data_horario" class="block text-gray-700 mb-2">Data/Horário:</label>
                    <input type="text" id="data_horario" name="data_horario" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Selecione a data e horário">
                </div>

                <!-- Declaração de Veracidade -->
                <div class="mb-4">
                    <label for="declaracao" class="block text-gray-700 mb-2">
                        <input type="checkbox" id="declaracao" name="declaracao" required>
                        Declaro que todas as informações fornecidas são verdadeiras.
                    </label>
                </div>

                <!-- Upload Documento de Identificação -->
                <div class="mb-4">
                    <label for="doc_identificacao" class="block text-gray-700 mb-2">
                        Documento de Identificação (PDF ou imagem):
                    </label>
                    <input type="file" id="doc_identificacao" name="doc_identificacao" required
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <p class="text-sm text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG (máx. 5MB)</p>
                </div>

                <!-- Upload Comprovante de Residência -->
                <div class="mb-4">
                    <label for="comp_residencia" class="block text-gray-700 mb-2">
                        Comprovante de Residência (PDF ou imagem):
                    </label>
                    <input type="file" id="comp_residencia" name="comp_residencia" required
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <p class="text-sm text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG (máx. 5MB)</p>
                </div>

                <button type="submit" id="submitButton"
                    class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                    <span class="flex items-center">
                        <i class="fas fa-check mr-2"></i>
                        <span>Enviar</span>
                    </span>
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inclua o Bootstrap JS para modais (se necessário) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    // Script para alternar o menu mobile
    const menuBtn = document.getElementById("menu-btn");
    const mobileMenu = document.getElementById("mobile-menu");

    menuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
    });

    // Inicializa o Flatpickr para o campo de data/hora
    flatpickr("#data_horario", {
        enableTime: true,
        dateFormat: "d/m/Y H:i",
        time_24hr: true,
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                longhand: ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira',
                    'Sexta-feira', 'Sábado'
                ],
            },
            months: {
                shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                longhand: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto',
                    'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                ],
            },
        }
    });

    // Modificar o envio do formulário para mostrar o loading
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const submitButton = document.getElementById('submitButton');
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processando...
        `;
        this.submit();
    });
    </script>
</body>

</html>