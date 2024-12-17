<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Multi-step Form - DEMUTRAN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../icon.png" type="image/png" />
    <style>
    .step {
        transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
    }

    .step.hidden {
        opacity: 0;
        transform: translateX(20px);
        pointer-events: none;
    }

    .progress-bar {
        transition: width 0.4s ease-in-out;
    }

    .progress-container {
        overflow: hidden;
    }
    </style>
</head>
<body class="min-h-screen bg-gray-100">
    <!-- Toast de Progresso -->
    <div id="toast-progress" class="fixed top-24 right-4 z-50 hidden flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
            </svg>
        </div>
        <div class="ms-3 text-sm font-normal" id="toast-message"></div>
        <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" onclick="closeToast()">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>

    <?php
    include 'scr/config.php';
    
    // Verifica se há um token na URL
    $token = isset($_GET['token']) ? $_GET['token'] : null;
    
    if ($token) {
        // Debug log
        echo "<script>console.log('Token found: " . $token . "');</script>";
        
        // Verifica cada tabela pela ordem
        $tables = ['DAT1', 'DAT2', 'vehicles'];
        $step = 1;
        
        foreach ($tables as $index => $table) {
            $sql = "SELECT token FROM $table WHERE token = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo "<script>console.log('Checking table: " . $table . "');</script>";
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "<script>console.log('Found token in " . $table . ": " . $row['token'] . "');</script>";
                
                // Remove a verificação de 'RL' e atualiza step baseado na tabela
                switch($table) {
                    case 'DAT1':
                        $step = 4;
                        echo "<script>console.log('Setting step to 4');</script>";
                        break;
                    case 'DAT2':
                        $step = 5;
                        echo "<script>console.log('Setting step to 5');</script>";
                        break;
                    case 'vehicles':
                        $step = 6;
                        echo "<script>console.log('Setting step to 6');</script>";
                        break;
                }
            }
        }
        
        echo "<script>console.log('Final step value: " . $step . "');</script>";
        
        if ($step > 1) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('Executing nextStep with value: " . $step . "');
                    nextStep($step);
                    showToast($step);
                });
            </script>";
        }
    }
    ?>
    <!-- Topbar -->
    <header class="bg-green-600 text-white shadow-md w-full fixed top-0 left-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Logo -->
            <div class="text-lg font-semibold flex items-center">
                <img src="../assets/icon.png" alt="DEMUTRAN" class="h-8 w-8 mr-2" />
                <a href="../" class="hover:text-green-300">DEMUTRAN</a>
            </div>

            <!-- Links -->
            <nav class="hidden md:flex space-x-6">
                <a href="../PCD/index.html" class="hover:text-green-300">Cartão Vaga Especial</a>
                <a href="../Defesa/index.html" class="hover:text-green-300">Defesa Prévia/JARI</a>
                <a href="../DAT/index.php" class="hover:text-green-300">DAT</a>
                <a href="../Parecer/index.php" class="hover:text-green-300">Parecer</a>
                <a href="../contato/index.html" class="hover:text-green-300">Contato</a>
            </nav>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button id="menu-btn" class="text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-green-600">
            <a href="../PCD/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Cartão Vaga Especial</a>
            <a href="../Defesa/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Defesa Prévia/JARI</a>
            <a href="../DAT/index.php" class="block px-4 py-2 text-white hover:bg-green-500">DAT</a>
            <a href="../Parecer/index.php" class="block px-4 py-2 text-white hover:bg-green-500">Parecer</a>
            <a href="../contato/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Contato</a>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <div class="pt-20 flex justify-center">
        <!-- Conteúdo aqui -->

        <script>
        // Script para alternar o menu mobile
        const menuBtn = document.getElementById("menu-btn");
        const mobileMenu = document.getElementById("mobile-menu");

        menuBtn.addEventListener("click", () => {
            mobileMenu.classList.toggle("hidden");
        });
        </script>
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-6xl">
            <!-- Modifiquei de max-w-lg para max-w-2xl -->
            <!-- Indicador de Página (Barra de Progresso) -->
            <div class="relative w-full bg-gray-200 rounded-full h-2 mb-6 progress-container">
                <div id="progress-bar" class="absolute top-0 h-2 bg-green-500 rounded-full progress-bar"
                    style="width: 0%"></div>
            </div>

            <div id="step-1" class="step">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Termos e Condições de Uso
                </h2>
                <div class="mb-4 text-gray-700">
                    <p class="mb-2">
                        Por favor, leia os seguintes termos e condições antes de
                        prosseguir:
                    </p>
                    <ul class="list-disc list-inside">
                        <li>
                            As informações prestadas serão utilizadas de maneira sigilosa
                            para fins estatísticos e de estudos objetivando a prevenção de
                            acidentes e o reforço da segurança pública;
                        </li>
                        <li>
                            O declarante é responsável pelas informações e pode sofrer
                            sanções penais/administrativas diante de informações falsas;
                        </li>
                        <li>
                            O DAT se propõe a registrar os casos de acidentes sem vítima,
                            que não tenha envolvido veículo de transporte de produtos
                            perigosos, do qual houve avaria ao compartimento de carga a
                            granel, derramamento ou vazamento do produto, nem veículos
                            públicos e que não tenham provocado dano ao meio ambiente ou ao
                            patrimônio público;
                        </li>
                        <li>
                            É necessária a utilização do Acrobat Reader para impressão da
                            declaração. Clique no ícone para efetuar o download do
                            <a href="https://get.adobe.com/br/reader/" class="text-green-500 underline"
                                target="_blank">Adobe Acrobat Reader</a>;
                        </li>
                        <li>O declarante deve ter mais de 18 anos ou ser emancipado;</li>
                        <li>
                            Serão registrados o IP e a data de abertura da declaração;
                        </li>
                        <li>
                            A declaração uma vez finalizada não terá mais possibilidade de
                            alteração, a não ser através do modo de retificação;
                        </li>
                        <li>
                            Caso o Sistema fique inativo pelo período de 1 hora, os dados
                            serão desconsiderados;
                        </li>
                        <li>
                            Após a geração de protocolo da declaração, o declarante terá o
                            prazo de 48 horas para concluir a inclusão das informações. Caso
                            este processo não seja efetuado dentro deste período de tempo, o
                            protocolo será desconsiderado;
                        </li>
                        <li>
                            Esta declaração estará sujeita à conferência para posterior
                            liberação. Você será informado via e-mail da aprovação da
                            declaração;
                        </li>
                        <li>
                            É obrigatória a indicação de um endereço eletrônico (e-mail)
                            para o preenchimento da declaração. Ele será o principal meio de
                            comunicação;
                        </li>
                        <li>
                            Você pode informar valores parciais para uma placa de veículo,
                            caso não tenha a identificação por completo. Exemplos:
                            'ABC12??', 'ABC??34', 'A??1234';
                        </li>
                        <li>Campos marcados com (*) são obrigatórios;</li>
                        <li>
                            Campos de ajuda (?) estão disponíveis no formulário, utilize-os
                            em caso de dúvida;
                        </li>
                    </ul>
                </div>

                <div class="flex items-center mb-4">
                    <input type="checkbox" id="agree" class="mr-2" />
                    <label for="agree" class="text-gray-700">Li e concordo com os termos e condições de uso</label>
                </div>

                <button id="continue-btn"
                    class="bg-green-500 text-white px-6 py-3 rounded w-full font-semibold hover:bg-green-600 disabled:opacity-50"
                    disabled onclick="">
                    Aceitar e Continuar
                </button>
            </div>

            <!-- Modal para Solicitar o Gmail -->
            <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="emailModalLabel">Informe seu email</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label for="userEmail" class="form-label">Digite seu E-mail Email:</label>
                            <input type="email" class="form-control" id="userEmail" placeholder="exemplo@gmail.com"
                                required>
                        </div>
                        <div class="modal-footer">
                            <!-- Alterar btn-primary para btn-success -->
                            <button type="button" class="btn btn-success" id="submitEmailBtn">Enviar e
                                Continuar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Token -->
            <div class="modal fade" id="tokenModal" tabindex="-1" aria-labelledby="tokenModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="tokenModalLabel">Código de Preenchimento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Guarde este código, ele será usado para retomar o preenchimento do formulário:</p>
                            <div class="alert alert-primary" role="alert">
                                <strong id="tokenDisplay"></strong>
                            </div>
                            <p>Tenha em mente que ao prosseguir, você concorda com os termos de uso mencionados
                                anteriormente.</p>
                        </div>
                        <div class="modal-footer">
                            <!-- Alterar btn-primary para btn-success -->
                            <button type="button" class="btn btn-success" id="startFormBtn">Iniciar Formulário</button>
                        </div>
                    </div>
                </div>
            </div>


            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            <script>
            // apenas test
            // document.getElementById('agree').addEventListener('change', function() {
            //     nextStep(6)
            // });

            // Habilitar botão "Aceitar e Continuar" ao marcar o checkbox
            document.getElementById('agree').addEventListener('change', function() {
                document.getElementById('continue-btn').disabled = !this.checked;
            });


            // Ao clicar no botão "Aceitar e Continuar", abrir modal para pedir o Gmail
            document.getElementById('continue-btn').addEventListener('click', function() {
                var emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
                emailModal.show();
            });

            // Enviar o e-mail e gerar o token
            document.getElementById('submitEmailBtn').addEventListener('click', async function() {
                const submitBtn = document.getElementById('submitEmailBtn');
                const email = document.getElementById('userEmail').value;
                const nome = "Usuário";

                if (!email || !email.includes('@gmail.com')) {
                    alert('Por favor, insira um Gmail válido.');
                    return;
                }

                // Desabilitar botão e mudar texto
                submitBtn.disabled = true; 
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando...';

                try {
                    // Gerar token
                    const tokenResponse = await fetch('generate_token.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            gmail: email,
                            nome: nome
                        })
                    });
                    
                    const data = await tokenResponse.json();
                    
                    if (data.success) {
                        // Enviar email
                        await fetch('../utils/mail.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                'email': email,
                                'nome': nome,
                                'assunto': 'Seu Token de Acesso DEMUTRAN',
                                'mensagem': `
                                    <html>
                                    <body style='font-family: Arial, sans-serif;'>
                                        <div style='background-color: #f5f5f5; padding: 20px;'>
                                            <h2 style='color: #2c5282;'>Token de Acesso Gerado</h2>
                                            <p>Prezado(a) usuário(a),</p>
                                            <p>Seu token de acesso foi gerado com sucesso para continuar o preenchimento do Sistema de Declaração de Acidente de Trânsito - DAT!</p>
                                            <p style='word-break: break-all;'><strong>Seu Email:</strong> ${email}</p>
                                            <p><strong>Token:</strong> ${data.token}</p>
                                            <div style='margin: 20px 0; text-align: center;'>
                                                <a href='http://localhost/demutran/DAT/index.php?token=${data.token}' 
                                                   style='background-color: #48bb78; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                                                    Continuar Preenchimento
                                                </a>
                                            </div>
                                            <hr style='border: 1px solid #e2e8f0;'>
                                            <p><strong>IMPORTANTE:</strong></p>
                                            <ul style='margin-left: 20px; color: #e53e3e;'>
                                                <li>Guarde este token com segurança</li>
                                                <li>Este token é exclusivo para seu preenchimento</li>
                                                <li>Este é um e-mail automático, não responda</li>
                                                <li>O token é válido por 48 horas</li>
                                                <li>Clique no botão acima ou use o token para continuar seu preenchimento</li>
                                            </ul>
                                        </div>
                                    </body>
                                    </html>`
                            })
                        });

                        // Fechar modal de email e mostrar token
                        var emailModal = bootstrap.Modal.getInstance(document.getElementById('emailModal'));
                        emailModal.hide();
                        document.getElementById('tokenDisplay').innerText = data.token;
                        var tokenModal = new bootstrap.Modal(document.getElementById('tokenModal'));
                        tokenModal.show();
                    } else {
                        throw new Error('Erro ao gerar token');
                    }
                } catch (error) {
                    alert('Erro ao processar sua solicitação');
                    console.error('Erro:', error);
                } finally {
                    // Restaurar botão ao estado original
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Enviar e Continuar';
                }
            });

            // Redirecionar para o formulário com o token
            document.getElementById('startFormBtn').addEventListener('click', function() {
                // Obter o token gerado
                const token = document.getElementById('tokenDisplay').innerText;

                // Atualizar a URL sem redirecionar
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('token', token);
                window.history.pushState({}, '', currentUrl);

                // Chamar a função nextStep(2) após adicionar o token à URL
                var tokenModal = bootstrap.Modal.getInstance(document.getElementById('tokenModal'));
                tokenModal.hide();
                nextStep(2);
            });
            </script>


            <div id="step-2" class="step hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Etapa 2: Perguntas de Adesão
                </h2>
                <!-- Pergunta 1 -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">
                        Este acidente ocorreu em uma rodovia/estrada Federal (BR)
                        atendida pela PRF?
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question1" value="Sim" id="q1-sim" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Sim
                            </div>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question1" value="Não" id="q1-nao" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Não
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Pergunta 2 -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">
                        Você é maior de 18 anos ou emancipado civilmente?
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question2" value="Sim" id="q2-sim" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Sim
                            </div>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question2" value="Não" id="q2-nao" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Não
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Pergunta 3 -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">
                        Alguém feriu-se, ainda que levemente, nesse acidente?
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question3" value="Sim" id="q3-sim" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Sim
                            </div>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question3" value="Não" id="q3-nao" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Não
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Pergunta 4 -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">
                        O acidente envolveu mais de 5(cinco) veículos?
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question4" value="Sim" id="q4-sim" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Sim
                            </div>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question4" value="Não" id="q4-nao" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Não
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Pergunta 5 -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">
                        O acidente envolveu veículo de transporte de produtos perigosos,
                        do qual houve avaria ao compartimento de carga a granel,
                        derramamento ou vazamento do produto?
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question5" value="Sim" id="q5-sim" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Sim
                            </div>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="question5" value="Não" id="q5-nao" class="hidden peer" />
                            <div
                                class="px-4 py-2 rounded border border-gray-300 peer-checked:bg-green-500 peer-checked:text-white">
                                Não
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Mensagem de erro -->
                <div id="error-message" class="hidden"></div>

                <!-- Botão de Próximo -->
                <button id="next-button" type="button"
                    class="bg-green-500 text-white px-6 py-3 rounded w-full font-semibold hover:bg-green-600 mt-4 disabled:opacity-50"
                    disabled onclick="nextStep(3)"> Próximo</button>

                </button>
                </form>
            </div>

            <script>
            document.querySelectorAll('input[type="radio"]').forEach((input) => {
                input.addEventListener("change", validateAdherence);
            });

            function validateAdherence() {
                const q1 = document.querySelector(
                    'input[name="question1"]:checked'
                );
                const q2 = document.querySelector(
                    'input[name="question2"]:checked'
                );
                const q3 = document.querySelector(
                    'input[name="question3"]:checked'
                );
                const q4 = document.querySelector(
                    'input[name="question4"]:checked'
                );
                const q5 = document.querySelector(
                    'input[name="question5"]:checked'
                );
                const errorMessage = document.getElementById("error-message");
                const nextButton = document.getElementById("next-button");

                let message = "";
                let isValid = true;

                if (q1 && q1.value === "Sim") {
                    message =
                        'Para esta declaração de acidente, clique <a href="https://declarante.prf.gov.br/declarante/" class="text-green-500 underline" target="_blank">aqui</a> para ser direcionado para o Sistema de Declaração de Acidente de Trânsito - DAT da PRF.';
                    isValid = false;
                } else if (q2 && q2.value === "Não") {
                    message =
                        "Somente pessoas emancipadas ou maiores de 18 anos podem realizar a declaração do acidente.";
                    isValid = false;
                } else if (
                    (q3 && q3.value === "Sim") ||
                    (q4 && q4.value === "Sim") ||
                    (q5 && q5.value === "Sim")
                ) {
                    message =
                        "Este boletim não pode ser feito eletronicamente. Favor entrar em contato com Secretaria de Segurança Pública, Defesa Civil, Mobilidade Urbana e Trânsito - SESDEM.";
                    isValid = false;
                } else if (!(q1 && q2 && q3 && q4 && q5)) {
                    message = "Por favor, responda a todas as perguntas.";
                    isValid = false;
                } else {
                    message = "";
                    isValid = true;
                }

                if (isValid) {
                    errorMessage.classList.add("hidden");
                    nextButton.disabled = false;
                } else {
                    errorMessage.innerHTML = `
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
          <span class="block sm:inline">${message}</span>
        </div>`;
                    errorMessage.classList.remove("hidden");
                    nextButton.disabled = true;
                }
            }
            </script> 



            <div id="step-3" class="step hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Etapa 3: Informações Pessoais e do Acidente
                </h2>
                <form id="form-info" action>
                    <!-- Relação com o veículo -->
                    <div class="mb-4">
                        <label for="relacao-veiculo" class="block text-gray-700 mb-2">
                            Indique a sua relação com o veículo principal:
                        </label>
                        <select id="relacao-veiculo" name="relacao_com_veiculo"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" selected hidden>Selecione</option>
                            <option value="Condutor">Condutor</option>
                            <option value="Corretor">Corretor</option>
                            <option value="Proprietário">Proprietário</option>
                            <option value="Passageiro">Passageiro</option>
                            <option value="Condutor e Proprietário">
                                Condutor e Proprietário
                            </option>
                            <option value="Terceiro Atingido">Terceiro Atingido</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <!-- Estrangeiro -->
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="estrangeiro" name="estrangeiro" class="mr-2" />
                            Estrangeiro?


                        </label>
                    </div>

                    <!-- Campos para estrangeiro -->
                    <div id="estrangeiro-info" class="hidden">
                        <div class="mb-4">
                            <label for="tipo-documento" class="block text-gray-700 mb-2">
                                Tipo de documento:
                            </label>
                            <input type="text" id="tipo-documento" name="tipo_documento"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label for="numero-documento" class="block text-gray-700 mb-2">
                                Número do documento:
                            </label>
                            <input type="text" id="numero-documento" name="numero_documento"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label for="pais-documento" class="block text-gray-700 mb-2">
                                País:
                            </label>
                            <input type="text" id="pais-documento" name="pais"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>
                    </div>

                    <!-- Nome -->
                    <div class="mb-4">
                        <label for="nome" class="block text-gray-700 mb-2"> Nome: </label>
                        <input type="text" id="nome" name="nome"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <!-- CPF -->
                    <div class="mb-4">
                        <label for="cpf" class="block text-gray-700 mb-2"> CPF: </label>
                        <input type="text" id="cpf" name="cpf"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <!-- Profissão -->
                    <div class="mb-4">
                        <label for="profissao" class="block text-gray-700 mb-2">
                            Profissão:
                        </label>
                        <input type="text" id="profissao" name="profissao"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <!-- Sexo -->
                    <div class="mb-4">
                        <label for="sexo" class="block text-gray-700 mb-2"> Sexo: </label>
                        <select id="sexo" name="sexo"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" disabled selected>Selecione</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                            <!-- <option value="Outro">Outro</option> -->
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <!-- Data de Nascimento -->
                    <div class="mb-4">
                        <label for="data-nascimento" class="block text-gray-700 mb-2">
                            Data de Nascimento:
                        </label>
                        <input type="date" id="data-nascimento" name="data_nascimento"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <!-- E-mail -->
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 mb-2">
                            E-mail:
                        </label>
                        <input type="email" id="email" name="email"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <!-- Celular -->
                    <div class="mb-4">
                        <label for="celular" class="block text-gray-700 mb-2">
                            Celular:
                        </label>
                        <input type="tel" id="celular" name="celular"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <!-- Endereço -->
                    <div class="mb-4">
                        <label for="cep" class="block text-gray-700 mb-2"> CEP: </label>
                        <input type="text" id="cep" name="cep"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="logradouro" class="block text-gray-700 mb-2">
                            Logradouro:
                        </label>
                        <input type="text" id="logradouro" name="logradouro"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="numero" class="block text-gray-700 mb-2">
                            Número:
                        </label>
                        <input type="text" id="numero" name="numero"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="complemento" class="block text-gray-700 mb-2">
                            Complemento:
                        </label>
                        <input type="text" id="complemento" name="complemento"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="bairro" class="block text-gray-700 mb-2">
                            Bairro/Localidade:
                        </label>
                        <input type="text" id="bairro" name="bairro_localidade"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="cidade" class="block text-gray-700 mb-2">
                            Cidade:
                        </label>
                        <input type="text" id="cidade" name="cidade"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="uf" class="block text-gray-700 mb-2">
                            UF (Estado):
                        </label>
                        <select id="uf" name="uf"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <!-- Lista de estados brasileiros -->
                            <option value="" disabled selected>Selecione</option>
                            <option value="AC">AC</option>
                            <option value="AL">AL</option>
                            <option value="AM">AM</option>
                            <option value="AP">AP</option>
                            <option value="BA">BA</option>
                            <option value="CE">CE</option>
                            <option value="DF">DF</option>
                            <option value="ES">ES</option>
                            <option value="GO">GO</option>
                            <option value="MA">MA</option>
                            <option value="MT">MT</option>
                            <option value="MS">MS</option>
                            <option value="MG">MG</option>
                            <option value="PA">PA</option>
                            <option value="PB">PB</option>
                            <option value="PR">PR</option>
                            <option value="PE">PE</option>
                            <option value="PI">PI</option>
                            <option value="RJ">RJ</option>
                            <option value="RN">RN</option>
                            <option value="RS">RS</option>
                            <option value="RO">RO</option>
                            <option value="RR">RR</option>
                            <option value="SC">SC</option>
                            <option value="SP">SP</option>
                            <option value="SE">SE</option>
                            <option value="TO">TO</option>
                            <!-- <option value="Outros">Outros</option> -->
                        </select>
                    </div>

                    <!-- Dados do acidente -->
                    <div class="mb-4">
                        <label for="data-acidente" class="block text-gray-700 mb-2">
                            Data do acidente:
                        </label>
                        <input type="date" id="data-acidente" name="data"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="horario-acidente" class="block text-gray-700 mb-2">
                            Horário do acidente:
                        </label>
                        <input type="time" id="horario-acidente" name="horario"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="cidade-acidente" class="block text-gray-700 mb-2">
                            Cidade do acidente:
                        </label>
                        <input type="text" id="cidade-acidente" value="Pau dos Ferros" name="cidade_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="uf-acidente" class="block text-gray-700 mb-2">
                            UF do acidente:
                        </label>
                        <input type="text" id="uf-acidente" value="RN" name="uf_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="cep-acidente" class="block text-gray-700 mb-2">
                            CEP do acidente:
                        </label>
                        <input type="text" id="cep-acidente" name="cep_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="logradouro-acidente" class="block text-gray-700 mb-2">
                            Logradouro do acidente:
                        </label>
                        <input type="text" id="logradouro-acidente" name="logradouro_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="numero-acidente" class="block text-gray-700 mb-2">
                            Número do local do acidente:
                        </label>
                        <input type="text" id="numero-acidente" name="numero_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="complemento-acidente" class="block text-gray-700 mb-2">
                            Complemento do local do acidente:
                        </label>
                        <input type="text" id="complemento-acidente" name="complemento_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="bairro-acidente" class="block text-gray-700 mb-2">
                            Bairro/Localidade do acidente:
                        </label>
                        <input type="text" id="bairro-acidente" name="bairro_localidade_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="ponto-referencia" class="block text-gray-700 mb-2">
                            Ponto de Referência:
                        </label>
                        <input type="text" id="ponto-referencia" name="ponto_referencia_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <!-- Condições da Via -->
                    <div class="mb-4">
                        <label for="condicoes-via" class="block text-gray-700 mb-2">
                            Condições da Via:
                        </label>
                        <input type="text" id="condicoes-via" name="condicoes_via"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="sinalizacao" class="block text-gray-700 mb-2">
                            Sinalização Horizontal e Vertical:
                        </label>
                        <input type="text" id="sinalizacao" name="sinalizacao_horizontal_vertical"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="tracado-via" class="block text-gray-700 mb-2">
                            Traçado da Via:
                        </label>
                        <input type="text" id="tracado-via" name="tracado_via"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="condicoes-meteorologicas" class="block text-gray-700 mb-2">
                            Condições Meteorológicas:
                        </label>
                        <input type="text" id="condicoes-meteorologicas" name="condicoes_meteorologicas"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <!-- Tipo de acidente -->
                    <div class="mb-4">
                        <label for="tipo-acidente" class="block text-gray-700 mb-2">
                            Tipo de acidente:
                        </label>
                        <select id="tipo-acidente" name="tipo_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" disabled selected>Selecione</option>
                            <option value="Atropelamento de Animal">
                                Atropelamento de Animal
                            </option>
                            <option value="Colisão Frontal">Colisão Frontal</option>
                            <option value="Colisão Lateral">Colisão Lateral</option>
                            <option value="Colisão Traseira">Colisão Traseira</option>
                            <option value="Colisão Transversal">Colisão Transversal</option>
                            <option value="Colisão com Objeto Estático">
                                Colisão com Objeto Estático
                            </option>
                            <option value="Colisão com Objeto Móvel">
                                Colisão com Objeto Móvel
                            </option>
                            <option value="Derramamento de Carga">
                                Derramamento de Carga
                            </option>
                            <option value="Saída de Pista">Saída de Pista</option>
                            <option value="Tombamento">Tombamento</option>
                            <option value="Engavetamento">Engavetamento</option>
                            <option value="Danos Eventuais">Danos Eventuais</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <!-- Botão de Submissão -->
                    <button type="submit"
                        class="bg-green-500 text-white px-6 py-3 rounded w-full font-semibold hover:bg-green-600 mt-4">
                        proximo
                    </button>
                </form>


                <script>
                // Função para obter o valor do parâmetro "token" da URL
                function getTokenFromURL() {
                    const urlParams = new URLSearchParams(window.location.search);
                    return urlParams.get('token'); // Obtém o valor do parâmetro 'token'
                }

                console.log('Script carregado corretamente');

                // Evento para manipular o checkbox estrangeiro
                document.getElementById("estrangeiro").addEventListener("change", function() {
                    const estrangeiroInfo = document.getElementById("estrangeiro-info");
                    if (this.checked) {
                        estrangeiroInfo.classList.remove("hidden");
                        document.getElementById("tipo-documento").setAttribute("required", "true");
                        document.getElementById("numero-documento").setAttribute("required", "true");
                        document.getElementById("pais-documento").setAttribute("required", "true");
                    } else {
                        estrangeiroInfo.classList.add("hidden");
                        document.getElementById("tipo-documento").removeAttribute("required");
                        document.getElementById("numero-documento").removeAttribute("required");
                        document.getElementById("pais-documento").removeAttribute("required");
                    }
                });

                // Manipulador de submissão do formulário
                const formInfo = document.getElementById('form-info');
                formInfo.addEventListener('submit', async function(event) {
                    event.preventDefault(); // Previne o recarregamento da página
                    console.log('Formulário enviado');

                    // Obtenha o token da URL
                    const token = getTokenFromURL();

                    // Crie um objeto FormData para enviar os dados do formulário
                    const formData = new FormData(formInfo);

                    // Adicione o token ao FormData
                    if (token) {
                        formData.append('token',
                            token); // Adiciona o token como um novo campo no formulário
                    }

                    // Exibe os dados que serão enviados (incluindo o token)
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`);
                    }

                    try {
                        const response = await fetch('Process_form/DAT1.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (response.ok) {
                            const result = await response.text();
                            alert(result);
                            nextStep(4);
                        } else {
                            alert('Ocorreu um erro ao enviar os dados. Tente novamente.');
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro no envio do formulário: ' + error.message); // Exibe o erro no alert
                    }
                });
                </script>

            </div>

            <div id="step-4" class="step hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Etapa 4: Informações do Veículo e Condutor
                </h2>
                <form id="form-veiculo-condutor">
                    <!-- Seção: VEÍCULO PRINCIPAL -->
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">
                        VEÍCULO PRINCIPAL
                    </h3>

                    <div class="mb-4">
                        <label for="situacao-veiculo" class="block text-gray-700 mb-2">
                            Situação do Veículo:
                        </label>
                        <select id="situacao-veiculo" name="situacao_veiculo"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" disabled selected>Selecione</option>
                            <option value="Licenciado no Brasil">
                                Licenciado no Brasil
                            </option>
                            <option value="Licenciado no Exterior">
                                Licenciado no Exterior
                            </option>
                            <option value="Não Registrado">Não Registrado</option>
                            <option value="Dispensado de Registro">
                                Dispensado de Registro
                            </option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="placa" class="block text-gray-700 mb-2">
                            Placa:
                        </label>
                        <input type="text" id="placa" name="placa"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="renavam" class="block text-gray-700 mb-2">
                            RENAVAM:
                        </label>
                        <input type="text" id="renavam" name="renavam"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="tipo-veiculo" class="block text-gray-700 mb-2">
                            Tipo de Veículo:
                        </label>
                        <select id="tipo-veiculo" name="tipo_veiculo"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" disabled selected>Selecione</option>
                            <option value="Automóvel">Automóvel</option>
                            <option value="Motocicleta">Motocicleta</option>
                            <option value="Caminhão">Caminhão</option>
                            <option value="Van">Van</option>
                            <option value="Ônibus">Ônibus</option>
                            <!-- <option value="Outro">Outro</option> -->
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="chassi" class="block text-gray-700 mb-2">
                            Chassi:
                        </label>
                        <input type="text" id="chassi" name="chassi"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="uf-veiculo" class="block text-gray-700 mb-2">
                            UF (Estado):
                        </label>
                        <select id="uf-veiculo" name="uf_veiculo"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <!-- Lista de estados brasileiros -->
                            <option value="" disabled selected>Selecione</option>
                            <option value="AC">AC</option>
                            <option value="AL">AL</option>
                            <option value="AM">AM</option>
                            <option value="AP">AP</option>
                            <option value="BA">BA</option>
                            <option value="CE">CE</option>
                            <option value="DF">DF</option>
                            <option value="ES">ES</option>
                            <option value="GO">GO</option>
                            <option value="MA">MA</option>
                            <option value="MT">MT</option>
                            <option value="MS">MS</option>
                            <option value="MG">MG</option>
                            <option value="PA">PA</option>
                            <option value="PB">PB</option>
                            <option value="PR">PR</option>
                            <option value="PE">PE</option>
                            <option value="PI">PI</option>
                            <option value="RJ">RJ</option>
                            <option value="RN">RN</option>
                            <option value="RS">RS</option>
                            <option value="RO">RO</option>
                            <option value="RR">RR</option>
                            <option value="SC">SC</option>
                            <option value="SP">SP</option>
                            <option value="SE">SE</option>
                            <option value="TO">TO</option>
                            <!-- <option value="Outros">Outros</option> -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="cor-veiculo" class="block text-gray-700 mb-2">
                            Cor:
                        </label>
                        <input type="text" id="cor-veiculo" name="cor_veiculo"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="marca-modelo" class="block text-gray-700 mb-2">
                            Marca/Modelo:
                        </label>
                        <input type="text" id="marca-modelo" name="marca_modelo"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="ano-modelo" class="block text-gray-700 mb-2">
                            Ano Modelo:
                        </label>
                        <input type="text" id="ano-modelo" name="ano_modelo"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="ano-fabricacao" class="block text-gray-700 mb-2">
                            Ano Fabricação:
                        </label>
                        <input type="text" id="ano-fabricacao" name="ano_fabricacao"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="categoria" class="block text-gray-700 mb-2">
                            Categoria:
                        </label>
                        <select id="categoria" name="categoria"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" disabled selected>Selecione</option>
                            <option value="Particular">Particular</option>
                            <option value="Coleção">Coleção</option>
                            <option value="Aprendizagem">Aprendizagem</option>
                            <option value="Diplomático">Diplomático</option>
                            <option value="Aluguel">Aluguel</option>
                            <option value="Experiência">Experiência</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="segurado" class="block text-gray-700 mb-2">
                            Segurado:
                        </label>
                        <select id="segurado" name="segurado"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" disabled selected>Selecione</option>
                            <option value="Sim">Sim</option>
                            <option value="Não">Não</option>
                            <option value="Não sei">Não sei</option>
                            <!-- <option value="Outros">Outros</option> -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="seguradora" class="block text-gray-700 mb-2">
                            Seguradora:
                        </label>
                        <input type="text" id="seguradora" name="seguradora"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="veiculo-articulado" class="block text-gray-700 mb-2">
                            Veículo articulado ou possui "carretinha"?
                        </label>
                        <select id="veiculo-articulado" name="veiculo_articulado"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" disabled selected>Selecione</option>
                            <option value="Não">Não</option>
                            <option value="Uma">Uma</option>
                            <option value="Duas">Duas</option>
                            <!-- <option value="Outros">Outros</option> -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="manobra-acidente" class="block text-gray-700 mb-2">
                            Manobra durante o acidente:
                        </label>
                        <select id="manobra-acidente" name="manobra_acidente"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" disabled selected>Selecione</option>
                            <option value="Estava na contramão">Estava na contramão</option>
                            <option value="Cruzava a via">Cruzava a via</option>
                            <option value="Entrava na via">Entrava na via</option>
                            <option value="Estava estacionado">Estava estacionado</option>
                            <option value="Estava em marcha ré">Estava em marcha ré</option>
                            <option value="Mudava de faixa">Mudava de faixa</option>
                            <option value="Estava parado na via">
                                Estava parado na via
                            </option>
                            <option value="Estava parado no acostamento">
                                Estava parado no acostamento
                            </option>
                            <option value="Estava efetuando retorno">
                                Estava efetuando retorno
                            </option>
                            <option value="Estava saindo da via">
                                Estava saindo da via
                            </option>
                            <option value="Seguia o fluxo">Seguia o fluxo</option>
                            <option value="Estava ultrapassando">
                                Estava ultrapassando
                            </option>
                            <option value="Virava à direita">Virava à direita</option>
                            <option value="Virava à esquerda">Virava à esquerda</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <!-- Seção: Formulário do Condutor -->
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">
                        Formulário do Condutor
                    </h3>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="nao-habilitado" name="nao_habilitado" class="mr-2" />
                            Não Habilitado?
                        </label>
                    </div>

                    <div id="condutor-info">
                        <div class="mb-4">
                            <label for="numero-registro" class="block text-gray-700 mb-2">
                                Número do Registro:
                            </label>
                            <input type="text" id="numero-registro" name="numero_registro"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label for="uf-cnh" class="block text-gray-700 mb-2">
                                UF CNH:
                            </label>
                            <select id="uf-cnh" name="uf_cnh"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <!-- Lista de estados brasileiros -->
                                <option value="" disabled selected>Selecione</option>
                                <option value="AC">AC</option>
                                <option value="AL">AL</option>
                                <option value="AM">AM</option>
                                <option value="AP">AP</option>
                                <option value="BA">BA</option>
                                <option value="CE">CE</option>
                                <option value="DF">DF</option>
                                <option value="ES">ES</option>
                                <option value="GO">GO</option>
                                <option value="MA">MA</option>
                                <option value="MT">MT</option>
                                <option value="MS">MS</option>
                                <option value="MG">MG</option>
                                <option value="PA">PA</option>
                                <option value="PB">PB</option>
                                <option value="PR">PR</option>
                                <option value="PE">PE</option>
                                <option value="PI">PI</option>
                                <option value="RJ">RJ</option>
                                <option value="RN">RN</option>
                                <option value="RS">RS</option>
                                <option value="RO">RO</option>
                                <option value="RR">RR</option>
                                <option value="SC">SC</option>
                                <option value="SP">SP</option>
                                <option value="SE">SE</option>
                                <option value="TO">TO</option>
                                <!-- <option value="Outros">Outros</option> -->
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="categoria-cnh" class="block text-gray-700 mb-2">
                                Categoria CNH:
                            </label>
                            <input type="text" id="categoria-cnh" name="categoria_cnh"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label for="data-1habilitacao" class="block text-gray-700 mb-2">
                                Data 1ª Habilitação:
                            </label>
                            <input type="date" id="data-1habilitacao" name="data_1habilitacao"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label for="validade-cnh" class="block text-gray-700 mb-2">
                                Validade:
                            </label>
                            <input type="date" id="validade-cnh" name="validade_cnh"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>
                    </div>

                    <!-- Estrangeiro -->
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="estrangeiro-condutor" name="estrangeiro-condutor" class="mr-2" />
                            Estrangeiro?
                        </label>
                    </div>

                    <!-- Campos para estrangeiro -->
                    <div id="estrangeiro-info-condutor" class="hidden">
                        <div class="mb-4">
                            <label for="tipo-documento-condutor" class="block text-gray-700 mb-2">
                                Tipo de documento:
                            </label>
                            <select id="tipo-documento-condutor" name="tipo_documento_condutor"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="" disabled selected>Selecione</option>
                                <option value="Passaporte">Passaporte</option>
                                <!-- <option value="Outro">Outro</option> -->
                                <option value="Outros">Outro</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="numero-documento-condutor" class="block text-gray-700 mb-2">
                                Número do documento:
                            </label>
                            <input type="text" id="numero-documento-condutor" name="numero_documento_condutor"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label for="pais-documento-condutor" class="block text-gray-700 mb-2">
                                País:
                            </label>
                            <input type="text" id="pais-documento-condutor" name="pais_documento_condutor"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="nome-condutor" class="block text-gray-700 mb-2">
                            Nome:
                        </label>
                        <input type="text" id="nome-condutor" name="nome_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="cpf-condutor" class="block text-gray-700 mb-2">
                            CPF:
                        </label>
                        <input type="text" id="cpf-condutor" name="cpf_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="sexo-condutor" class="block text-gray-700 mb-2">
                            Sexo:
                        </label>
                        <select id="sexo-condutor" name="sexo_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="" disabled selected>Selecione</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                            <!-- <option value="Outro">Outro</option> -->
                            <option value="Outros">Outro</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="nascimento-condutor" class="block text-gray-700 mb-2">
                            Nasc.:
                        </label>
                        <input type="date" id="nascimento-condutor" name="nascimento_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="email-condutor" class="block text-gray-700 mb-2">
                            E-mail:
                        </label>
                        <input type="email" id="email-condutor" name="email_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="celular-condutor" class="block text-gray-700 mb-2">
                            Celular:
                        </label>
                        <input type="tel" id="celular-condutor" name="celular_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="cep-condutor" class="block text-gray-700 mb-2">
                            CEP:
                        </label>
                        <input type="text" id="cep-condutor" name="cep_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="logradouro-condutor" class="block text-gray-700 mb-2">
                            Logradouro:
                        </label>
                        <input type="text" id="logradouro-condutor" name="logradouro_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="numero-condutor" class="block text-gray-700 mb-2">
                            Número:
                        </label>
                        <input type="text" id="numero-condutor" name="numero_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="complemento-condutor" class="block text-gray-700 mb-2">
                            Complemento:
                        </label>
                        <input type="text" id="complemento-condutor" name="complemento_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="bairro-condutor" class="block text-gray-700 mb-2">
                            Bairro/Localidade:
                        </label>
                        <input type="text" id="bairro-condutor" name="bairro_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="cidade-condutor" class="block text-gray-700 mb-2">
                            Cidade:
                        </label>
                        <input type="text" id="cidade-condutor" name="cidade_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>

                    <div class="mb-4">
                        <label for="uf-condutor" class="block text-gray-700 mb-2">
                            UF:
                        </label>
                        <select id="uf-condutor" name="uf_condutor"
                            class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <!-- Lista de estados brasileiros -->
                            <option value="" disabled selected>Selecione</option>
                            <option value="AC">AC</option>
                            <option value="AL">AL</option>
                            <option value="AM">AM</option>
                            <option value="AP">AP</option>
                            <option value="BA">BA</option>
                            <option value="CE">CE</option>
                            <option value="DF">DF</option>
                            <option value="ES">ES</option>
                            <option value="GO">GO</option>
                            <option value="MA">MA</option>
                            <option value="MT">MT</option>
                            <option value="MS">MS</option>
                            <option value="MG">MG</option>
                            <option value="PA">PA</option>
                            <option value="PB">PB</option>
                            <option value="PR">PR</option>
                            <option value="PE">PE</option>
                            <option value="PI">PI</option>
                            <option value="RJ">RJ</option>
                            <option value="RN">RN</option>
                            <option value="RS">RS</option>
                            <option value="RO">RO</option>
                            <option value="RR">RR</option>
                            <option value="SC">SC</option>
                            <option value="SP">SP</option>
                            <option value="SE">SE</option>
                            <option value="TO">TO</option>
                            <!-- <option value="Outros">Outros</option> -->
                        </select>
                    </div>

                    <!-- Seção: Danos no Veículo -->
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">
                        Danos no Veículo
                    </h3>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="danos-sistema-seguranca" name="danos-sistema-seguranca"
                                class="mr-2" />
                            Houve danos ao sistema de segurança, freios, direção ou de
                            suspensão do veículo?
                        </label>
                    </div>

                    <div id="danos-partes" class="hidden">
                        <label class="block text-gray-700 mb-2">
                            Selecionar Partes Danificadas:
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Dianteira Direita"
                                    class="mr-2" /> Dianteira Direita
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Dianteira Esquerda"
                                    class="mr-2" /> Dianteira Esquerda
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Lateral/Teto Direito"
                                    class="mr-2" /> Lateral/Teto Direito
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Lateral/Teto Esquerdo"
                                    class="mr-2" /> Lateral/Teto Esquerdo
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Traseira Direita"
                                    class="mr-2" /> Traseira Direita
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Traseira Esquerda"
                                    class="mr-2" /> Traseira Esquerda
                            </label>
                        </div>
                    </div>


                    <!-- Seção: Danos na Carga do Veículo -->
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">
                        Danos na Carga do Veículo
                    </h3>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="danos-carga" name="danos_carga" class="mr-2" />
                            Houve danos na carga do veículo?
                        </label>
                    </div>

                    <div id="danos-carga-info" class="hidden">
                        <div class="mb-4">
                            <label for="numero-notas" class="block text-gray-700 mb-2">
                                Nº das Notas Fiscais, Manifestos ou Equivalentes:
                            </label>
                            <input type="text" id="numero-notas" name="numero_notas"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label for="tipo-mercadoria" class="block text-gray-700 mb-2">
                                Tipo de Mercadoria:
                            </label>
                            <input type="text" id="tipo-mercadoria" name="tipo_mercadoria"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label for="valor-mercadoria" class="block text-gray-700 mb-2">
                                Valor Total:
                            </label>
                            <input type="text" id="valor-mercadoria" name="valor_mercadoria"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label for="extensao-danos" class="block text-gray-700 mb-2">
                                Extensão estimada dos danos da carga:
                            </label>
                            <input type="text" id="extensao-danos" name="extensao_danos"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="tem-seguro-carga" name="tem_seguro_carga" class="mr-2" />
                                Tem seguro?
                            </label>
                        </div>

                        <div id="seguradora-carga-info" class="hidden">
                            <label for="seguradora-carga" class="block text-gray-700 mb-2">
                                Informe a Seguradora:
                            </label>
                            <input type="text" id="seguradora-carga" name="seguradora_carga"
                                class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                        </div>
                    </div>

                    <!-- Botão de Submissão -->
                    <button type="submit"
                        class="bg-green-500 text-white px-6 py-3 rounded w-full font-semibold hover:bg-green-600 mt-4">
                        proximo
                    </button>
                </form>
            </div>

            <script>
            // Condutor: Campos relacionados à CNH
            document
                .getElementById("nao-habilitado")
                .addEventListener("change", function() {
                    const condutorInfo = document.getElementById("condutor-info");
                    if (this.checked) {
                        condutorInfo.classList.add("hidden");
                        document
                            .querySelectorAll(
                                "#condutor-info input, #condutor-info select"
                            )
                            .forEach((el) => {
                                el.removeAttribute("required");
                            });
                    } else {
                        condutorInfo.classList.remove("hidden");
                        document
                            .querySelectorAll(
                                "#condutor-info input, #condutor-info select"
                            )
                            .forEach((el) => {
                                el.setAttribute("required", "true");
                            });
                    }
                });

            // Condutor: Campos relacionados ao Estrangeiro
            document
                .getElementById("estrangeiro-condutor")
                .addEventListener("change", function() {
                    const estrangeiroInfoCondutor = document.getElementById(
                        "estrangeiro-info-condutor"
                    );
                    if (this.checked) {
                        estrangeiroInfoCondutor.classList.remove("hidden");
                        document
                            .querySelectorAll(
                                "#estrangeiro-info-condutor input, #estrangeiro-info-condutor select"
                            )
                            .forEach((el) => {
                                el.setAttribute("required", "true");
                            });
                    } else {
                        estrangeiroInfoCondutor.classList.add("hidden");
                        document
                            .querySelectorAll(
                                "#estrangeiro-info-condutor input, #estrangeiro-info-condutor select"
                            )
                            .forEach((el) => {
                                el.removeAttribute("required");
                            });
                    }
                });

            // Danos no Veículo: Partes danificadas
            document
                .getElementById("danos-sistema-seguranca")
                .addEventListener("change", function() {
                    const danosPartes = document.getElementById("danos-partes");
                    if (this.checked) {
                        danosPartes.classList.remove("hidden");
                    } else {
                        danosPartes.classList.add("hidden");
                    }
                });

            // Danos na Carga: Campos relacionados à carga
            document
                .getElementById("danos-carga")
                .addEventListener("change", function() {
                    const danosCargaInfo =
                        document.getElementById("danos-carga-info");
                    if (this.checked) {
                        danosCargaInfo.classList.remove("hidden");
                        document
                            .querySelectorAll("#danos-carga-info input")
                            .forEach((el) => {
                                el.setAttribute("required", "true");
                            });
                    } else {
                        danosCargaInfo.classList.add("hidden");
                        document
                            .querySelectorAll("#danos-carga-info input")
                            .forEach((el) => {
                                el.removeAttribute("required");
                            });
                    }
                });

            // Danos na Carga: Seguradora
            document
                .getElementById("tem-seguro-carga")
                .addEventListener("change", function() {
                    const seguradoraCargaInfo = document.getElementById(
                        "seguradora-carga-info"
                    );
                    if (this.checked) {
                        seguradoraCargaInfo.classList.remove("hidden");
                        document
                            .getElementById("seguradora-carga")
                            .setAttribute("required", "true");
                    } else {
                        seguradoraCargaInfo.classList.add("hidden");
                        document
                            .getElementById("seguradora-carga")
                            .removeAttribute("required");
                    }
                });

            // logica para chamar o php e processar o form

            // Manipulador de submissão do formulário
            const formcond = document.getElementById('form-veiculo-condutor');
            formcond.addEventListener('submit', async function(event) {
                event.preventDefault(); // Previne o recarregamento da página
                console.log('Formulário enviado');

                // Obtenha o token da URL
                const token = getTokenFromURL();

                // Crie um objeto FormData para enviar os dados do formulário
                const formData = new FormData(formcond);

                // Adicione o token ao FormData
                if (token) {
                    formData.append('token',
                        token); // Adiciona o token como um novo campo no formulário
                }

                const checkboxes = document.querySelectorAll('input[name="partes_danificadas[]"]:checked');
                let partesSelecionadas = [];
                checkboxes.forEach(checkbox => {
                    partesSelecionadas.push(checkbox
                        .value); // Adiciona o valor dos checkboxes selecionados ao array
                });

                // Junta as partes selecionadas em uma string separada por vírgulas
                formData.append('partes_danificadas', partesSelecionadas.join(','));

                // Exibe os dados que serão enviados (incluindo o token)
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }

                try {
                    const response = await fetch('Process_form/DAT2.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        const result = await response.text();
                        alert(result);
                        nextStep(5);
                    } else {
                        alert('Ocorreu um erro ao enviar os dados. Tente novamente.');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro no envio do formulário: ' + error.message); // Exibe o erro no alert
                }
            });
            </script>
            <div id="step-5" class="step hidden">
                <form id="form-veiculo">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                        Etapa 5: Cadastro de Veículos Envolvidos
                        <p class="text-sm text-gray-600 mb-4">
                            Para adicionar veículos envolvidos, clique no botão "Adicionar Veículo" e preencha as
                            informações solicitadas sobre danos ao veículo e à carga, se aplicável.
                        </p>


                    </h2>

                    <div id="vehicle-container">
                        <!-- Container onde os veículos serão adicionados -->
                    </div>

                    <button id="add-vehicle-btn" type="button"
                        class="bg-green-500 text-white px-6 py-3 rounded font-semibold hover:bg-green-600 mb-6">
                        Adicionar Veículo
                    </button>


                    <button id="submit-data-btn" type="button"
                        class="bg-green-500 text-white px-6 py-3 rounded font-semibold hover:bg-green-600">
                        Concluir
                    </button>
                </form>
            </div>

            <script>
            let vehicleCount = 0;

            function addVehicle() {
                vehicleCount++;
                const vehicleHTML = `
      <div class="vehicle-form bg-gray-100 p-4 rounded-lg shadow-md mb-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-700">Veículo ${vehicleCount}</h3>
          <button type="button" class="toggle-vehicle-details text-green-500 underline">Minimizar</button>
        </div>
        <div class="vehicle-details hidden">
          <!-- Danos no veículo -->
          <div class="mb-4">
            <label class="flex items-center">
              <input type="checkbox" name="damage_system_${vehicleCount}" class="mr-2 damage-checkbox"> Houve danos ao sistema de segurança, freios, direção ou de suspensão do veículo?
            </label>
            <div class="damage-parts hidden mt-2">
              <label class="block text-gray-700 mb-2">Selecionar Partes Danificadas:</label>
              <div class="grid grid-cols-2 gap-4">
                <label class="flex items-center">
                  <input type="checkbox" name="parte_danificada_dianteira_direita_${vehicleCount}" class="mr-2"> Dianteira Direita
                </label>
                <label class="flex items-center">
                  <input type="checkbox" name="parte_danificada_dianteira_esquerda_${vehicleCount}" class="mr-2"> Dianteira Esquerda
                </label>
                <label class="flex items-center">
                  <input type="checkbox" name="parte_danificada_lateral_direita_${vehicleCount}" class="mr-2"> Lateral/Teto Direito
                </label>
                <label class="flex items-center">
                  <input type="checkbox" name="parte_danificada_lateral_esquerda_${vehicleCount}" class="mr-2"> Lateral/Teto Esquerdo
                </label>
                <label class="flex items-center">
                  <input type="checkbox" name="parte_danificada_traseira_direita_${vehicleCount}" class="mr-2"> Traseira Direita
                </label>
                <label class="flex items-center">
                  <input type="checkbox" name="parte_danificada_traseira_esquerda_${vehicleCount}" class="mr-2"> Traseira Esquerda
                </label>
              </div>
            </div>
          </div>

          <!-- Danos na carga do veículo -->
          <div class="mb-4">
            <label class="flex items-center">
              <input type="checkbox" name="load_damage_${vehicleCount}" class="mr-2 load-damage-checkbox"> Houve danos na carga do veículo?
            </label>
            <div class="load-damage-info hidden mt-2">
              <div class="mb-4">
                <label class="block text-gray-700 mb-2">Nº das Notas Fiscais, Manifestos ou Equivalentes:</label>
                <input type="text" name="nota_fiscal_${vehicleCount}" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 mb-2">Tipo de Mercadoria:</label>
                <input type="text" name="tipo_mercadoria_${vehicleCount}" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 mb-2">Valor Total:</label>
                <input type="text" name="valor_total_${vehicleCount}" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
              </div>
              <div class="mb-4">
                <label class="block text-gray-700 mb-2">Extensão estimada dos danos:</label>
                <input type="text" name="estimativa_danos_${vehicleCount}" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
              </div>
              <div class="mb-4">
                <label class="flex items-center">
                  <input type="checkbox" name="has_insurance_${vehicleCount}" class="mr-2 load-insurance-checkbox"> Tem seguro?
                </label>
                <div class="load-insurance-info hidden mt-2">
                  <label class="block text-gray-700 mb-2">Informe a Seguradora:</label>
                  <input type="text" name="seguradora_${vehicleCount}" class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;

                const container = document.getElementById("vehicle-container");
                container.insertAdjacentHTML("beforeend", vehicleHTML);

                // Adiciona interatividade para expandir e colapsar
                const lastVehicleForm = container.lastElementChild;
                const toggleBtn = lastVehicleForm.querySelector(
                    ".toggle-vehicle-details"
                );
                const detailsDiv = lastVehicleForm.querySelector(".vehicle-details");
                detailsDiv.classList.remove("hidden"); // Remover a classe hidden para manter o veículo expandido


                toggleBtn.addEventListener("click", () => {
                    detailsDiv.classList.toggle("hidden");
                    toggleBtn.textContent = detailsDiv.classList.contains("hidden") ?
                        "Expandir" :
                        "Minimizar";
                });

                // Interatividade para o checkbox de danos no veículo
                const damageCheckbox =
                    lastVehicleForm.querySelector(".damage-checkbox");
                const damagePartsDiv =
                    lastVehicleForm.querySelector(".damage-parts");
                damageCheckbox.addEventListener("change", () => {
                    damagePartsDiv.classList.toggle(
                        "hidden",
                        !damageCheckbox.checked
                    );
                });

                // Interatividade para o checkbox de danos na carga
                const loadDamageCheckbox = lastVehicleForm.querySelector(
                    ".load-damage-checkbox"
                );
                const loadDamageInfoDiv =
                    lastVehicleForm.querySelector(".load-damage-info");
                loadDamageCheckbox.addEventListener("change", () => {
                    loadDamageInfoDiv.classList.toggle(
                        "hidden",
                        !loadDamageCheckbox.checked
                    );
                });

                // Interatividade para o checkbox de seguro da carga
                const loadInsuranceCheckbox = lastVehicleForm.querySelector(
                    ".load-insurance-checkbox"
                );
                const loadInsuranceInfoDiv = lastVehicleForm.querySelector(
                    ".load-insurance-info"
                );
                loadInsuranceCheckbox.addEventListener("change", () => {
                    loadInsuranceInfoDiv.classList.toggle(
                        "hidden",
                        !loadInsuranceCheckbox.checked
                    );
                });
            }

            document
                .getElementById("add-vehicle-btn")
                .addEventListener("click", addVehicle);


            function logVehicleData() {
                const vehiclesData = [];
                const vehicleForms = document.querySelectorAll(".vehicle-form");

                // Captura o token da URL
                const urlParams = new URLSearchParams(window.location.search);
                const token = urlParams.get('token');
                event.preventDefault(); // Previne o recarregamento da página

                vehicleForms.forEach((vehicleForm, index) => {
                    const vehicleData = {};

                    const damageSystem = vehicleForm.querySelector(`input[name="damage_system_${index + 1}"]`)
                        .checked;
                    vehicleData.damageSystem = damageSystem;

                    const damagedParts = vehicleForm.querySelectorAll(
                        `input[name^="parte_danificada_"][name*="_${index + 1}"]`);
                    vehicleData.damagedParts = [];
                    damagedParts.forEach(part => {
                        vehicleData.damagedParts.push({
                            name: part.name,
                            checked: part.checked
                        });
                    });

                    const loadDamage = vehicleForm.querySelector(`input[name="load_damage_${index + 1}"]`)
                        .checked;
                    vehicleData.loadDamage = loadDamage;

                    if (loadDamage) {
                        const notaFiscal = vehicleForm.querySelector(`input[name="nota_fiscal_${index + 1}"]`)
                            .value;
                        const tipoMercadoria = vehicleForm.querySelector(
                            `input[name="tipo_mercadoria_${index + 1}"]`).value;
                        const valorTotal = vehicleForm.querySelector(`input[name="valor_total_${index + 1}"]`)
                            .value;
                        const estimativaDanos = vehicleForm.querySelector(
                            `input[name="estimativa_danos_${index + 1}"]`).value;

                        vehicleData.notaFiscal = notaFiscal;
                        vehicleData.tipoMercadoria = tipoMercadoria;
                        vehicleData.valorTotal = valorTotal;
                        vehicleData.estimativaDanos = estimativaDanos;

                        const hasInsurance = vehicleForm.querySelector(
                            `input[name="has_insurance_${index + 1}"]`).checked;
                        vehicleData.hasInsurance = hasInsurance;

                        if (hasInsurance) {
                            const seguradora = vehicleForm.querySelector(
                                `input[name="seguradora_${index + 1}"]`).value;
                            vehicleData.seguradora = seguradora;
                        }
                    }

                    vehiclesData.push(vehicleData);
                });

                fetch('Process_form/DAT3.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            vehiclesData,
                            token
                        }) // Inclui o token aqui
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data);
                        nextStep(6)
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                    });

            }

            // Adicionar o botão para enviar os dados
            document.getElementById("submit-data-btn").addEventListener("click", logVehicleData);
            </script>
            <div id="step-6" class="step hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Etapa 6: Narrativa e Informações Complementares
                </h2>

                <!-- Seção de Narrativa -->
                <div class="bg-gray-100 p-4 rounded-lg shadow-md mb-6">
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">
                        <h1 class="text-3xl font-bold mb-6 text-center text-green-600"> Narrativa Gerada </h1>

                        <!-- Conteúdo das tabelas -->
                        <div id="table-container"></div>
                    </h3>
                    <p id="narrative-text" class="text-gray-700"></p>
                </div>

                <!-- Checkboxes e Campos de Texto -->
                <form id="form-complementares">
                    <div class="mb-4">
                        <label class="flex items-center mb-2">
                            <input type="checkbox" id="patrimonio-checkbox" class="mr-2" />
                            Danos ao Patrimônio Público
                        </label>
                        <textarea id="patrimonio-text" name="patrimonio_text"
                            class="w-full border rounded-md p-2 bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-500"
                            disabled maxlength="1500"></textarea>
                        <p class="text-sm text-gray-500 text-right">
                            <span id="patrimonio-count">0</span>/1500 caracteres
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center mb-2">
                            <input type="checkbox" id="meio-ambiente-checkbox" class="mr-2" />
                            Danos ao Meio Ambiente
                        </label>
                        <textarea id="meio-ambiente-text" name="meio_ambiente_text"
                            class="w-full border rounded-md p-2 bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-500"
                            disabled maxlength="1500"></textarea>
                        <p class="text-sm text-gray-500 text-right">
                            <span id="meio-ambiente-count">0</span>/1500 caracteres
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center mb-2">
                            <input type="checkbox" id="informacoes-complementares-checkbox" class="mr-2" />
                            Informações Complementares
                        </label>
                        <textarea id="informacoes-complementares-text" name="informacoes_complementares_text"
                            class="w-full border rounded-md p-2 bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-500"
                            disabled maxlength="1500"></textarea>
                        <p class="text-sm text-gray-500 text-right">
                            <span id="informacoes-complementares-count">0</span>/1500 caracteres
                        </p>
                    </div>

                    <!-- Botões de Navegação -->
                    <div class="flex justify-between mt-6">
                        <button id="anterior-btn" type="submit"
                            class="bg-gray-500 text-white px-6 py-3 rounded font-semibold hover:bg-gray-600">
                            Anterior
                        </button>
                        <button id="proximo-btn" type="submit"
                            class="bg-green-500 text-white px-6 py-3 rounded font-semibold hover:bg-green-600">
                            > Próximo
                        </button>
                    </div>
                </form>
            </div>
            </form>
        </div>
    </div>
    </div>

    <script>
    // Função para carregar a narrativa gerada

    // Funções para habilitar/contar caracteres nos campos de texto
    function toggleTextField(checkbox, textField, counter) {
        checkbox.addEventListener("change", function() {
            if (this.checked) {
                textField.removeAttribute("disabled");
                textField.classList.remove("bg-gray-200");
                textField.classList.add("bg-white");
            } else {
                textField.setAttribute("disabled", "true");
                textField.classList.remove("bg-white");
                textField.classList.add("bg-gray-200");
                textField.value = "";
                counter.textContent = "0";
            }
        });

        textField.addEventListener("input", function() {
            counter.textContent = textField.value.length;
        });
    }

    // Aplicar a função toggleTextField para cada campo
    toggleTextField(
        document.getElementById("patrimonio-checkbox"),
        document.getElementById("patrimonio-text"),
        document.getElementById("patrimonio-count")
    );

    toggleTextField(
        document.getElementById("meio-ambiente-checkbox"),
        document.getElementById("meio-ambiente-text"),
        document.getElementById("meio-ambiente-count")
    );

    toggleTextField(
        document.getElementById("informacoes-complementares-checkbox"),
        document.getElementById("informacoes-complementares-text"),
        document.getElementById("informacoes-complementares-count")
    );

    // Navegação entre etapas
    // document
    //     .getElementById("anterior-btn")
    //     .addEventListener("click", function() {
    //         // Lógica para voltar à página anterior
    //         // Exemplo: window.location.href = 'form3.html';
    //     });

    // document
    //     .getElementById("proximo-btn")
    //     .addEventListener("click", function() {
    //         // Lógica para avançar à próxima página
    //         // Exemplo: window.location.href = 'finalizacao.html';
    //     });
    // Função para obter o token da URL
    // Função para obter o token da URL


    // Função para enviar o token ao PHP e obter os dados das tabelas
    function fetchTableData() {
        const token = getTokenFromURL();

        // Envia o token para o PHP usando Fetch API
        fetch('Process_form/fetch_tables.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `token=${token}`
            })
            .then(response => response.text())
            .then(data => {
                // Exibe as tabelas retornadas no div 'table-container'
                document.getElementById('table-container').innerHTML = data;
            })
            .catch(error => console.error('Erro:', error));
    }

    // Chama a função ao carregar a página
    window.onload = function() {
        fetchTableData();
        // nextStep(6);
    };
    </script>

    </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto text-center shadow-lg transform transition-all duration-300 ease-out scale-105">
            <svg class="w-16 h-16 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 id="modal-title" class="text-3xl font-semibold mb-4 text-gray-800">Declaração Enviada!</h2>
            <div id="modal-message" class="text-gray-600 mb-6">
                <p class="mb-2">Sua Declaração de Acidente de Trânsito (DAT) foi registrada com sucesso!</p>
                <p class="mb-2">Em instantes você receberá um e-mail de confirmação com os detalhes da sua declaração.</p>
                <p class="mb-2">Lembre-se de verificar também sua caixa de spam.</p>
                <div class="bg-blue-50 p-4 rounded-lg mt-4">
                    <p class="text-blue-800 text-sm">
                        <strong>Próximos passos:</strong>
                        <ul class="list-disc text-left pl-5 mt-2">
                            <li>Sua declaração será analisada pela equipe técnica</li>
                            <li>Você receberá atualizações por e-mail</li>
                            <li>Guarde seu token para consultas futuras</li>
                        </ul>
                    </p>
                </div>
            </div>
            <button onclick="closeModal()" class="bg-green-600 text-white font-medium px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition duration-200 w-full">
                Entendi
            </button>
        </div>
    </div>

    <script>
    const agreeCheckbox = document.getElementById("agree");
    const continueBtn = document.getElementById("continue-btn");

    agreeCheckbox.addEventListener("change", function() {
        if (agreeCheckbox.checked) {
            continueBtn.disabled = false;
        } else {
            continueBtn.disabled = true;
        }
    });

    function nextStep(step) {
        document.querySelectorAll(".step").forEach(function(stepDiv) {
            stepDiv.classList.add("hidden");
        });
        document.getElementById("step-" + step).classList.remove("hidden");
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });

        // Atualiza a barra de progresso
        const progressBar = document.getElementById("progress-bar");
        const width = (step - 1) * 16.6666667;
        progressBar.style.width = `${width}%`;

        if (step === 6) {
            fetchTableData();
        }
    }

    // Função para mostrar o toast
    function showToast(step) {
        const toast = document.getElementById('toast-progress');
        const message = document.getElementById('toast-message');
        message.textContent = `Progresso restaurado para a etapa ${step}`;
        toast.classList.remove('hidden');
        
        // Esconder o toast após 3 segundos
        setTimeout(() => {
            closeToast();
        }, 3000);
    }

    function closeToast() {
        const toast = document.getElementById('toast-progress');
        toast.classList.add('hidden');
    }

    // function submitForm() {
    // Função para inicializar o listener do formulário
    window.addEventListener('load', function() {
        initializeFormListener();
        initializeEventListener();
    });

    function initializeEventListener() {
        const proximoBtn = document.getElementById('proximo-btn');
        proximoBtn.addEventListener('click', function() {
            console.log('log');
        });
    }

    function initializeFormListener() {
        const form = document.getElementById('form-complementares');

        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                const patrimonioCheckbox = document.getElementById('patrimonio-checkbox').checked;
                const patrimonioText = document.getElementById('patrimonio-text').value;

                const meioAmbienteCheckbox = document.getElementById('meio-ambiente-checkbox').checked;
                const meioAmbienteText = document.getElementById('meio-ambiente-text').value;

                const informacoesComplementaresCheckbox = document.getElementById('informacoes-complementares-checkbox').checked;
                const informacoesComplementaresText = document.getElementById('informacoes-complementares-text').value;

                const token = getTokenFromURL();
                const formData = new FormData();
                formData.append('patrimonio_checkbox', patrimonioCheckbox);
                formData.append('patrimonio_text', patrimonioText);
                formData.append('meio_ambiente_checkbox', meioAmbienteCheckbox);
                formData.append('meio_ambiente_text', meioAmbienteText);
                formData.append('informacoes_complementares_checkbox', informacoesComplementaresCheckbox);
                formData.append('informacoes_complementares_text', informacoesComplementaresText);
                formData.append('token', token);

                // Primeiro, envia os dados do formulário
                fetch('Process_form/DAT4.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    // Após salvar os dados, envia o e-mail de confirmação
                    return fetch('Process_form/send_confirmation_email.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `token=${token}`
                    });
                })
                .then(response => response.text())
                .then(emailResult => {
                    console.log('E-mail enviado:', emailResult);
                    showModal();
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
            });
        } else {
            console.error('Formulário não encontrado');
        }
    }


    function showModal(success) {
        const modal = document.getElementById("modal");
        const title = document.getElementById("modal-title");
        const message = document.getElementById("modal-message");
        title.innerText = "Sucesso!";
        message.innerText = "Seu formulário foi enviado com sucesso.";


        modal.classList.remove("hidden");
    }

    function closeModal() {
        const modal = document.getElementById("modal");
        modal.classList.add("hidden");
        window.location.href = 'index.php';
    }
    
    </script>
</body>

</html>