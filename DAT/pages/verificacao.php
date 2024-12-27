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
                    disabled> Próximo</button>

                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[type="radio"]').forEach((input) => {
    input.addEventListener("change", validateAllQuestions);
});

function validateAllQuestions() {
    const errorMessage = document.getElementById("error-message");
    const nextButton = document.getElementById("next-button");

    // Array de validações em ordem de prioridade
    const validations = [{
            question: 'question1',
            value: 'Sim',
            message: 'Para esta declaração de acidente, clique <a href="https://declarante.prf.gov.br/declarante/" class="text-green-500 underline" target="_blank">aqui</a> para ser direcionado para o Sistema de Declaração de Acidente de Trânsito - DAT da PRF.'
        },
        {
            question: 'question2',
            value: 'Não',
            message: 'Somente pessoas emancipadas ou maiores de 18 anos podem realizar a declaração do acidente.'
        },
        {
            question: 'question3',
            value: 'Sim',
            message: 'Este boletim não pode ser feito eletronicamente. Favor entrar em contato com Secretaria de Segurança Pública, Defesa Civil, Mobilidade Urbana e Trânsito - SESDEM.'
        },
        {
            question: 'question4',
            value: 'Sim',
            message: 'Este boletim não pode ser feito eletronicamente. Favor entrar em contato com Secretaria de Segurança Pública, Defesa Civil, Mobilidade Urbana e Trânsito - SESDEM.'
        },
        {
            question: 'question5',
            value: 'Sim',
            message: 'Este boletim não pode ser feito eletronicamente. Favor entrar em contato com Secretaria de Segurança Pública, Defesa Civil, Mobilidade Urbana e Trânsito - SESDEM.'
        }
    ];

    let lastErrorMessage = "";
    let isValid = true;

    // Verifica cada questão em ordem
    for (const validation of validations) {
        const answer = document.querySelector(`input[name="${validation.question}"]:checked`);
        if (answer && answer.value === validation.value) {
            lastErrorMessage = validation.message;
            isValid = false;
            break;
        }
    }

    // Se não houver respostas inválidas, verifica se todas foram respondidas
    if (isValid) {
        const allAnswers = Array.from(document.querySelectorAll('input[type="radio"]:checked'));

        // Verifica se todas as 5 perguntas foram respondidas
        if (allAnswers.length === 5) {
            // Verifica se Q1 é "Não", Q2 é "Sim", e Q3, Q4, Q5 são "Não"
            const responses = {
                question1: document.querySelector('input[name="question1"]:checked')?.value,
                question2: document.querySelector('input[name="question2"]:checked')?.value,
                question3: document.querySelector('input[name="question3"]:checked')?.value,
                question4: document.querySelector('input[name="question4"]:checked')?.value,
                question5: document.querySelector('input[name="question5"]:checked')?.value
            };

            if (responses.question1 === "Não" &&
                responses.question2 === "Sim" &&
                responses.question3 === "Não" &&
                responses.question4 === "Não" &&
                responses.question5 === "Não") {
                errorMessage.classList.add("hidden");
                nextButton.disabled = false;
                return;
            }
        }
    }

    // Se chegou aqui, ou tem erro ou faltam respostas
    if (lastErrorMessage) {
        errorMessage.innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">${lastErrorMessage}</span>
            </div>`;
        errorMessage.classList.remove("hidden");
    }
    nextButton.disabled = true;
}

// Evento do botão próximo
document.getElementById('next-button').addEventListener('click', function() {
    if (!this.disabled) {
        const token = new URLSearchParams(window.location.search).get('token');
        window.location.href = `dados_gerais.php?token=${token}`;
    }
});
</script>

<?php
require_once(__DIR__ . '/../includes/footer.php');
?>