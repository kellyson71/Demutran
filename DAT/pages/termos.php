<?php
require_once(__DIR__ . '/../includes/header.php');

// Define a etapa atual antes de incluir o progresso
$currentStep = 0;
?>

<div class="pt-20 flex justify-center flex-col items-center">
    <div class="container mx-auto px-4 max-w-6xl">
        <?php require_once(__DIR__ . '/../includes/progresso.php'); ?>

        <div class="bg-white p-8 rounded-lg shadow-lg w-full">
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
                        <li>As informações prestadas serão utilizadas de maneira sigilosa para fins estatísticos e de
                            estudos
                            objetivando a prevenção de acidentes e o reforço da segurança pública;</li>
                        <li>O declarante é responsável pelas informações e pode sofrer sanções penais/administrativas
                            diante
                            de
                            informações falsas;</li>
                        <li>O DAT se propõe a registrar os casos de acidentes sem vítima, que não tenha envolvido
                            veículo de
                            transporte de produtos perigosos, do qual houve avaria ao compartimento de carga a granel,
                            derramamento
                            ou vazamento do produto, nem veículos públicos e que não tenham provocado dano ao meio
                            ambiente
                            ou ao
                            patrimônio público;</li>
                        <li>É necessária a utilização do Acrobat Reader para impressão da declaração. Clique no ícone
                            para
                            efetuar o
                            download do <a href="https://get.adobe.com/br/reader/" class="text-green-500 underline"
                                target="_blank">Adobe Acrobat Reader</a>;</li>
                        <li>O declarante deve ter mais de 18 anos ou ser emancipado;</li>
                        <li>Serão registrados o IP e a data de abertura da declaração;</li>
                        <li>A declaração uma vez finalizada não terá mais possibilidade de alteração, a não ser através
                            do
                            modo de
                            retificação;</li>
                        <li>Caso o Sistema fique inativo pelo período de 1 hora, os dados serão desconsiderados;</li>
                        <li>Após a geração de protocolo da declaração, o declarante terá o prazo de 48 horas para
                            concluir a
                            inclusão das informações. Caso este processo não seja efetuado dentro deste período de
                            tempo, o
                            protocolo será desconsiderado;</li>
                        <li>Esta declaração estará sujeita à conferência para posterior liberação. Você será informado
                            via
                            e-mail da
                            aprovação da declaração;</li>
                        <li>É obrigatória a indicação de um endereço eletrônico (e-mail) para o preenchimento da
                            declaração.
                            Ele
                            será o principal meio de comunicação;</li>
                        <li>Você pode informar valores parciais para uma placa de veículo, caso não tenha a
                            identificação
                            por
                            completo. Exemplos: 'ABC12??', 'ABC??34', 'A??1234';</li>
                        <li>Campos marcados com (*) são obrigatórios;</li>
                        <li>Campos de ajuda (?) estão disponíveis no formulário, utilize-os em caso de dúvida;</li>
                    </ul>
                </div>

                <div class="flex items-center mb-4">
                    <input type="checkbox" id="agree" class="mr-2" />
                    <label for="agree" class="text-gray-700">Li e concordo com os termos e condições de uso</label>
                </div>

                <button id="continue-btn"
                    class="bg-green-500 text-white px-6 py-3 rounded w-full font-semibold hover:bg-green-600 disabled:opacity-50"
                    disabled>
                    Aceitar e Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const agreeCheckbox = document.getElementById("agree");
    const continueBtn = document.getElementById("continue-btn");

    agreeCheckbox.addEventListener("change", function() {
        continueBtn.disabled = !this.checked;
    });

    // Ao clicar no botão "Aceitar e Continuar", redirecionar para verificação
    document.getElementById('continue-btn').addEventListener('click', function() {
        window.location.href = 'verificacao.php';
    });
</script>

<?php
require_once(__DIR__ . '/../includes/footer.php');
?>