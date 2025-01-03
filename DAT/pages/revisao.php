<?php
// Verifica se há um token na URL antes de qualquer output
$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$token) {
    header('Location: termos.php');
    exit;
}

// Define a etapa atual antes de incluir o progresso
$currentStep = 6;

require_once(__DIR__ . '/../../env/config.php');

// Agora que temos a conexão, podemos verificar se o formulário já foi enviado
$sql = "SELECT * FROM DAT4 WHERE token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$formJaEnviado = $result->num_rows > 0;
$stmt->close();

// Passar essa informação para o JavaScript
echo "<script>const formJaEnviado = " . ($formJaEnviado ? 'true' : 'false') . ";</script>";

require_once(__DIR__ . '/../includes/header.php');
?>

<div class="pt-20 flex justify-center flex-col items-center">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Adiciona a barra de progresso -->
        <?php require_once(__DIR__ . '/../includes/progresso.php'); ?>

        <div class="bg-white p-8 rounded-lg shadow-lg w-full">
            <div id="step-6" class="step">
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
        fetch('../Process_form/fetch_tables.php', {
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

    function initializeFormListener() {
        const form = document.getElementById('form-complementares');

        if (form) {
            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                // Se o formulário já foi enviado, mostra o modal e retorna
                if (formJaEnviado) {
                    showModal(true);
                    return;
                }

                const token = getTokenFromURL();
                const formData = new FormData(form);
                formData.append('token', token);

                try {
                    const response = await fetch('../Process_form/DAT4.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('Erro ao salvar os dados');
                    }

                    // Removendo a parte do envio de email separado já que ele está sendo feito no DAT4.php
                    showModal();

                } catch (error) {
                    console.error('Erro:', error);
                    alert('Ocorreu um erro ao processar sua solicitação: ' + error.message);
                }
            });
        }
    }

    function showModal(jaEnviado = false) {
        const modal = document.getElementById("modal");
        const title = document.getElementById("modal-title");
        const message = document.getElementById("modal-message");

        if (jaEnviado) {
            title.innerText = "Formulário já enviado!";
            message.innerHTML = `
                <p class="mb-2">Este formulário já foi enviado anteriormente.</p>
                <p class="mb-2">Você será redirecionado para a página inicial.</p>
            `;
            // Remover a seção de "Próximos passos" se já foi enviado
            const proximosPassos = message.querySelector('.bg-blue-50');
            if (proximosPassos) {
                proximosPassos.remove();
            }
        } else {
            title.innerText = "Declaração Enviada!";
            // Manter a mensagem original para novos envios
        }

        modal.classList.remove("hidden");
    }

    function closeModal() {
        const modal = document.getElementById("modal");
        if (modal) {
            modal.classList.add("hidden");
            setTimeout(() => {
                window.location.href = '../../index.php';
            }, 500);
        }
    }

    // Inicializa os listeners quando a página carregar
    window.addEventListener('load', function() {
        initializeFormListener();
    });
</script>

<!-- Modal -->
<div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center hidden">
    <div
        class="bg-white rounded-lg p-8 max-w-md mx-auto text-center shadow-lg transform transition-all duration-300 ease-out scale-105">
        <svg class="w-16 h-16 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
        <button onclick="closeModal()"
            class="bg-green-600 text-white font-medium px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition duration-200 w-full">
            Entendi
        </button>
    </div>
</div>

<?php
require_once(__DIR__ . '/../includes/footer.php');
?>
</body>

</html>