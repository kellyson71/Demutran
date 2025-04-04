<?php
require_once(__DIR__ . '/../includes/header.php');

// Define a etapa atual antes de incluir o progresso
$currentStep = 1;
?>

<div class="pt-20 flex justify-center flex-col items-center">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Adiciona max-w-6xl para limitar largura -->
        <?php require_once(__DIR__ . '/../includes/progresso.php'); ?>

        <div class="bg-white p-8 rounded-lg shadow-lg w-full">
            <div id="step-2" class="step">
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
                    <div id="error-question1"
                        class="hidden mt-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
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
                    <div id="error-question2"
                        class="hidden mt-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
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
                    <div id="error-question3"
                        class="hidden mt-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
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
                    <div id="error-question4"
                        class="hidden mt-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
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
                    <div id="error-question5"
                        class="hidden mt-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    </div>
                </div>

                <!-- Botão de Próximo -->
                <button id="next-button" type="button"
                    class="bg-green-500 text-white px-6 py-3 rounded w-full font-semibold hover:bg-green-600 mt-4 disabled:opacity-50"
                    disabled> Próximo</button>

                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Solicitar o Email -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">Informe seu email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="userEmail" class="form-label">Digite seu E-mail:</label>
                <input type="email" class="form-control" id="userEmail" placeholder="exemplo@email.com" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="submitEmailBtn">Enviar e Continuar</button>
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
                <p>Tenha em mente que ao prosseguir, você concorda com os termos de uso mencionados anteriormente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="startFormBtn">Iniciar Formulário</button>
            </div>
        </div>
    </div>
</div>

<script>
    const mensagensErro = {
        question1: 'Para esta declaração de acidente, clique <a href="https://declarante.prf.gov.br/declarante/" class="text-green-500 underline" target="_blank">aqui</a> para ser direcionado para o Sistema de Declaração de Acidente de Trânsito - DAT da PRF.',
        question2: 'Somente pessoas emancipadas ou maiores de 18 anos podem realizar a declaração do acidente.',
        question3: 'Este boletim não pode ser feito eletronicamente. Favor entrar em contato com Secretaria de Segurança Pública, Defesa Civil, Mobilidade Urbana e Trânsito - SESDEM.',
        question4: 'Este boletim não pode ser feito eletronicamente. Favor entrar em contato com Secretaria de Segurança Pública, Defesa Civil, Mobilidade Urbana e Trânsito - SESDEM.',
        question5: 'Este boletim não pode ser feito eletronicamente. Favor entrar em contato com Secretaria de Segurança Pública, Defesa Civil, Mobilidade Urbana e Trânsito - SESDEM.'
    };

    const respostasInvalidas = {
        question1: 'Sim',
        question2: 'Não',
        question3: 'Sim',
        question4: 'Sim',
        question5: 'Sim'
    };

    function verificarResposta(questao) {
        const resposta = document.querySelector(`input[name="${questao}"]:checked`);
        const errorDiv = document.getElementById(`error-${questao}`);

        if (resposta) {
            if (resposta.value === respostasInvalidas[questao]) {
                errorDiv.innerHTML = mensagensErro[questao];
                errorDiv.classList.remove('hidden');
            } else {
                errorDiv.classList.add('hidden');
            }
        }

        verificarTodasRespostas();
    }

    function verificarTodasRespostas() {
        const todasQuestoes = ['question1', 'question2', 'question3', 'question4', 'question5'];
        const nextButton = document.getElementById('next-button');

        const todasRespondidas = todasQuestoes.every(questao => {
            const resposta = document.querySelector(`input[name="${questao}"]:checked`);
            return resposta && resposta.value !== respostasInvalidas[questao];
        });

        nextButton.disabled = !todasRespondidas;
    }

    // Adiciona os event listeners
    document.querySelectorAll('input[type="radio"]').forEach(input => {
        input.addEventListener('change', () => verificarResposta(input.name));
    });

    // Event listener do botão próximo
    document.getElementById('next-button').addEventListener('click', function() {
        if (!this.disabled) {
            var emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
            emailModal.show();
        }
    });

    // Enviar o e-mail e gerar o token
    document.getElementById('submitEmailBtn').addEventListener('click', async function() {
        const submitBtn = document.getElementById('submitEmailBtn');
        const email = document.getElementById('userEmail').value;
        const nome = "Usuário";

        if (!email || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            alert('Por favor, insira um endereço de e-mail válido.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando...';

        try {
            const tokenResponse = await fetch('../../DAT/Process_form/generate_token.php', {
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
                // Correção na chamada do sistema de e-mail
                const mailResponse = await fetch('../../utils/mail.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        nome: nome,
                        assunto: 'Seu Token de Acesso DEMUTRAN',
                        mensagem: `
                    <html>
                    <body style='font-family: Arial, sans-serif;'>
                        <div style='background-color: #f5f5f5; padding: 20px;'>
                            <h2 style='color: #2c5282;'>Token de Acesso Gerado</h2>
                            <p>Prezado(a) usuário(a),</p>
                            <p>Seu token de acesso foi gerado com sucesso para continuar o preenchimento do Sistema de Declaração de Acidente de Trânsito - DAT!</p>
                            <p style='word-break: break-all;'><strong>Seu Email:</strong> ${email}</p>
                            <p><strong>Token:</strong> ${data.token}</p>
                            <div style='margin: 20px 0; text-align: center;'>
                                <a href='http://localhost/demutran/DAT/escolher-dat.html?token=${data.token}' 
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

                const mailResult = await mailResponse.json();
                console.log('Resposta do servidor de email:', mailResult);

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
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Enviar e Continuar';
        }
    });

    // Redirecionar para o formulário com o token
    document.getElementById('startFormBtn').addEventListener('click', function() {
        const token = document.getElementById('tokenDisplay').innerText;
        window.location.href = `dados_gerais.php?token=${token}`;
    });
</script>

<?php
require_once(__DIR__ . '/../includes/footer.php');
?>