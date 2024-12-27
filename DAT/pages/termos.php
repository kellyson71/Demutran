<?php
require_once(__DIR__ . '/../includes/header.php');

// Define a etapa atual antes de incluir o progresso
$currentStep = 0 ;
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
                <input type="email" class="form-control" id="userEmail" placeholder="exemplo@gmail.com" required>
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
                <p>Tenha em mente que ao prosseguir, você concorda com os termos de uso mencionados
                    anteriormente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="startFormBtn">Iniciar Formulário</button>
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

    submitBtn.disabled = true;
    submitBtn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando...';

    try {
        const tokenResponse = await fetch(
            '../../DAT/Process_form/generate_token.php', { // Caminho corrigido
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
            await fetch('../../utils/mail.php', { // Caminho corrigido
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
    window.location.href = 'verificacao.php?token=' + token; // Alterado para verificacao.php
});

// Remover o onclick e adicionar o evento via JavaScript
document.getElementById('continue-btn').addEventListener('click', function() {
    var emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
    emailModal.show();
});
</script>

<?php
require_once(__DIR__ . '/../includes/footer.php');
?>
?>