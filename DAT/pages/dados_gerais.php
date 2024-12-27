<?php
require_once(__DIR__ . '/../includes/header.php');

// Define a etapa atual antes de incluir o progresso
$currentStep = 3;

// Verifica se há um token na URL
$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$token) {
    header('Location: termos.php');
    exit;
}

require_once('C:\wamp64\www\Demutran\env\config.php');
?>

<div class="pt-20 flex justify-center flex-col items-center">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Adiciona a barra de progresso -->
        <?php require_once(__DIR__ . '/../includes/progresso.php'); ?>

        <div class="bg-white p-8 rounded-lg shadow-lg w-full">
            <div id="step-3" class="step">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Etapa 3: Informações Pessoais e do Acidente
                </h2>

                <!-- Aqui começa seu formulário existente -->
                <form id="form-info">
                    <!-- Div para mensagens -->
                    <div id="message-container" class="mb-4 hidden">
                        <div class="p-4 rounded">
                            <p id="message-text"></p>
                        </div>
                    </div>

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
            </div>
        </div>
    </div>
</div>

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

    // Função para mostrar mensagem
    function showMessage(message, isError = false) {
        const container = document.getElementById('message-container');
        const messageText = document.getElementById('message-text');

        container.classList.remove('hidden');
        messageText.textContent = message;

        if (isError) {
            container.firstElementChild.classList.remove('bg-green-100', 'text-green-700');
            container.firstElementChild.classList.add('bg-red-100', 'text-red-700');
        } else {
            container.firstElementChild.classList.remove('bg-red-100', 'text-red-700');
            container.firstElementChild.classList.add('bg-green-100', 'text-green-700');
        }

        // Scroll para a mensagem
        container.scrollIntoView({
            behavior: 'smooth'
        });
    }

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
            formData.append('token', token); // Adiciona o token como um novo campo no formulário
        }

        // Exibe os dados que serão enviados (incluindo o token)
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        try {
            // Corrigindo o caminho do arquivo DAT1.php
            const response = await fetch('../Process_form/DAT1.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();

            try {
                // Tenta fazer parse do resultado como JSON
                const jsonResult = JSON.parse(result);
                showMessage(jsonResult.message, !jsonResult.success);

                if (jsonResult.success) {
                    // Aguarda 2 segundos antes de redirecionar
                    setTimeout(() => {
                        window.location.href = `infoveiculo.php?token=${token}`;
                    }, 2000);
                }
            } catch (e) {
                // Se não for JSON, mostra como texto normal
                showMessage(result, true);
            }
        } catch (error) {
            console.error('Erro:', error);
            showMessage('Erro ao enviar o formulário: ' + error.message, true);
        }
    });
</script>

<?php
require_once(__DIR__ . '/../includes/footer.php');
?>