<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Acidente de Trânsito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

</head>
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

<body class="min-h-screen bg-gray-100">
    <!-- Topbar -->
    <header class="bg-green-600 text-white shadow-md w-full fixed top-0 left-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Logo -->
            <div class="text-lg font-semibold">
                <a href="#" class="hover:text-green-300">DEMUTRAN</a>
            </div>

            <!-- Links -->
            <nav class="hidden md:flex space-x-6">
                <a href="#" class="hover:text-green-300">Cartão Vaga Especial</a>
                <a href="#" class="hover:text-green-300">Defesa Prévia/JARI</a>
                <a href="#" class="hover:text-green-300">DAT</a>
                <a href="#" class="hover:text-green-300">Contato</a>
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
            <a href="#" class="block px-4 py-2 text-white hover:bg-green-500">Início</a>
            <as href="#" class="block px-4 py-2 text-white hover:bg-green-500">Sobre</as>
            <a href="#" class="block px-4 py-2 text-white hover:bg-green-500">Serviços</a>
            <a href="#" class="block px-4 py-2 text-white hover:bg-green-500">Contato</a>
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

            <div class="relative w-full bg-gray-200 rounded-full h-2 mb-6 progress-container">
                <div id="progress-bar" class="absolute top-0 h-2 bg-green-500 rounded-full progress-bar"
                    style="width: 0%"></div>
            </div>

            <div id="step-1" class="step">
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
                        disabled onclick="nextStep(2)">
                        Aceitar e Continuar
                    </button>
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
                                anteriormente.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="startFormBtn">Iniciar Formulário</button>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            <script>
            document.getElementById('agree').addEventListener('change', function() {
                document.getElementById('continue-btn').disabled = !this.checked;
            });

            document.getElementById('continue-btn').addEventListener('click', function() {
                // Gerar token e salvar no servidor
                fetch('generate_token.php', {
                        method: 'POST',
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Exibir token no modal
                            document.getElementById('tokenDisplay').innerText = data.token;
                            var tokenModal = new bootstrap.Modal(document.getElementById('tokenModal'));
                            tokenModal.show();
                        } else {
                            alert('Erro ao gerar token');
                        }
                    });
            });

            document.getElementById('startFormBtn').addEventListener('click', function() {
                window.location.href = "form.php?token=" + document.getElementById('tokenDisplay')
                    .innerText;
            });
            </script>

</body>

</html>