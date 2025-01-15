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
            <div class="flex p-4 mb-4 text-green-800 rounded-lg bg-green-50" role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <div>
                    <span class="font-medium">Informações importantes:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        <li>A solicitação deverá ser feita no mínimo 04 dias antes do evento</li>
                        <li>O parecer será entregue presencialmente</li>
                    </ul>
                </div>
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

                <!-- Substitua o bloco de data e hora por este -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Data do Evento -->
                    <div>
                        <label for="data_evento" class="block text-gray-700 mb-2">Data do Evento:</label>
                        <input type="text" id="data_evento" name="data_evento" required
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Selecione a data">
                    </div>

                    <!-- Horário de Início -->
                    <div>
                        <label for="horario_inicio" class="block text-gray-700 mb-2">Horário de Início:</label>
                        <input type="text" id="horario_inicio" name="horario_inicio" required
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Selecione o horário">
                    </div>

                    <!-- Horário de Término -->
                    <div>
                        <label for="horario_fim" class="block text-gray-700 mb-2">Horário de Término:</label>
                        <input type="text" id="horario_fim" name="horario_fim" required
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Selecione o horário">
                    </div>
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
                        <i class="far fa-file-alt"></i> Anexar Documento de Identificação*:
                    </label>
                    <input type="file" id="doc_identificacao" name="doc_identificacao" accept=".pdf,.jpg,.jpeg,.png"
                        required
                        class="w-full text-gray-600 border rounded-md file:border-0 file:border-r border-gray-300 file:bg-gray-50 file:mr-4 file:py-2 file:px-4 hover:file:bg-gray-100 file:text-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p class="text-sm text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG (máx. 5MB)</p>
                </div>

                <!-- Upload Comprovante de Residência -->
                <div class="mb-4">
                    <label for="comp_residencia" class="block text-gray-700 mb-2">
                        <i class="far fa-file-alt"></i> Anexar Comprovante de Residência*:
                    </label>
                    <input type="file" id="comp_residencia" name="comp_residencia" accept=".pdf,.jpg,.jpeg,.png"
                        required
                        class="w-full text-gray-600 border rounded-md file:border-0 file:border-r border-gray-300 file:bg-gray-50 file:mr-4 file:py-2 file:px-4 hover:file:bg-gray-100 file:text-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
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

    <!-- Modal de Sucesso -->
    <div id="success-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-screen max-h-full bg-gray-900/50 backdrop-blur-sm">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-xl shadow-2xl transform transition-all">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-6 md:p-6 border-b rounded-t">
                    <div class="flex items-center gap-3">
                        <div class="bg-green-100 rounded-full p-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            Solicitação Enviada com Sucesso!
                        </h3>
                    </div>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center transition-colors duration-200"
                        data-modal-hide="success-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Fechar modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-6 md:p-6 space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-green-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-base leading-relaxed text-gray-600">
                            Sua solicitação de parecer foi enviada com sucesso! Nossa equipe analisará seu pedido e
                            retornará em breve.
                        </p>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-yellow-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                </path>
                            </svg>
                        </div>
                        <div class="text-base leading-relaxed text-gray-600">
                            <p class="font-medium">Número do Protocolo: <span id="protocolNumber"
                                    class="text-yellow-700"></span></p>
                            <p class="text-sm mt-1">Guarde este número para acompanhamento da sua solicitação</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-blue-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <p class="text-base leading-relaxed text-gray-600">
                            Um comprovante da sua solicitação será enviado para o email cadastrado (<span
                                class="font-medium" id="emailConfirmation"></span>). Por favor, verifique também sua
                            caixa de spam.
                        </p>
                    </div>
                </div>
                <!-- Modal footer -->
                <div
                    class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 p-6 md:p-6 border-t border-gray-200 rounded-b">
                    <button onclick="window.location.href='../'" type="button"
                        class="w-full sm:w-auto text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors duration-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar para Início
                    </button>
                    <button onclick="window.location.reload()" type="button"
                        class="w-full sm:w-auto py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-green-700 focus:z-10 focus:ring-4 focus:ring-gray-100 transition-colors duration-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Nova Solicitação
                    </button>
                </div>
            </div>
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

        // Configuração do Flatpickr para data
        flatpickr("#data_evento", {
            dateFormat: "d/m/Y",
            minDate: new Date().fp_incr(4),
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                    longhand: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
                },
                months: {
                    shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                    longhand: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto',
                        'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                    ],
                },
            }
        });

        // Configuração do Flatpickr para horários
        const configHorario = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 30,
        };

        flatpickr("#horario_inicio", configHorario);
        flatpickr("#horario_fim", configHorario);

        // Modificar o envio do formulário para mostrar o loading
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            submitButton.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processando...
        `;

            const formData = new FormData(form);

            fetch('processa_formulario.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mostrar o modal de sucesso
                        const modal = document.getElementById("success-modal");
                        modal.classList.remove("hidden");
                        modal.classList.add("flex");

                        // Adicionar o email ao texto de confirmação
                        const emailConfirmation = document.getElementById("emailConfirmation");
                        const emailInput = document.getElementById("email");
                        if (emailConfirmation && emailInput) {
                            emailConfirmation.textContent = emailInput.value;
                        }

                        // Atualizar o número do protocolo com o valor retornado do servidor
                        const protocolNumber = document.getElementById("protocolNumber");
                        if (protocolNumber) {
                            // Extrair o protocolo da mensagem de sucesso
                            const protocolo = data.message.split(": ")[1];
                            protocolNumber.textContent = protocolo;
                        }

                        // Limpar o formulário
                        form.reset();
                    } else {
                        throw new Error(data.message || "Erro ao processar a solicitação");
                    }
                })
                .catch(error => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = `
                <span class="flex items-center">
                    <i class="fas fa-check mr-2"></i>
                    <span>Enviar</span>
                </span>
            `;

                    // Criar feedback de erro
                    const feedback = document.createElement('div');
                    feedback.className =
                        'feedback-message fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg z-50';
                    feedback.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span>${error.message || 'Ocorreu um erro ao enviar o formulário.'}</span>
                </div>
            `;
                    document.body.appendChild(feedback);

                    setTimeout(() => {
                        feedback.remove();
                    }, 4000);
                });
        });

        // Adicionar funcionalidade para fechar o modal
        document.querySelectorAll('[data-modal-hide="success-modal"]').forEach(button => {
            button.addEventListener('click', () => {
                const modal = document.getElementById("success-modal");
                modal.classList.add("hidden");
                modal.classList.remove("flex");
            });
        });
    </script>
</body>

</html>