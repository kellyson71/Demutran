<?php
require_once(__DIR__ . '/../includes/header.php');

// Define a etapa atual antes de incluir o progresso
$currentStep = 4;

// Verifica se há um token na URL
$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$token) {
    header('Location: termos.php');
    exit;
}

require_once(__DIR__ . '/../../env/config.php'); 
?>

<div class="pt-20 flex justify-center flex-col items-center">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Adiciona a barra de progresso -->
        <?php require_once(__DIR__ . '/../includes/progresso.php'); ?>

        <div class="bg-white p-8 rounded-lg shadow-lg w-full">
            <!-- Conteúdo existente do step-4 -->
            <div id="step-4" class="step">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Etapa 4: Informações do Veículo e Condutor
                </h2>

                <!-- Adicionar div para mensagens logo após o título -->
                <div id="message-container" class="mb-4 hidden">
                    <div class="p-4 rounded">
                        <p id="message-text"></p>
                    </div>
                </div>

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
                            <option value="RO">RO"></option>
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
                                <option value="PE">PE"></option>
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
                                    class="mr-2" />
                                Dianteira Direita
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Dianteira Esquerda"
                                    class="mr-2" />
                                Dianteira Esquerda
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Lateral/Teto Direito"
                                    class="mr-2" />
                                Lateral/Teto Direito
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Lateral/Teto Esquerdo"
                                    class="mr-2" />
                                Lateral/Teto Esquerdo
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Traseira Direita"
                                    class="mr-2" /> Traseira
                                Direita
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="partes_danificadas[]" value="Traseira Esquerda"
                                    class="mr-2" />
                                Traseira Esquerda
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
        </div>
    </div>
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

    // Função para mostrar mensagem (adicionar antes do event listener do formulário)
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

    // Modificar o event listener do formulário
    const formcond = document.getElementById('form-veiculo-condutor');
    formcond.addEventListener('submit', async function(event) {
        event.preventDefault();
        console.log('Formulário enviado');

        const token = getTokenFromURL();
        const formData = new FormData(formcond);

        if (token) {
            formData.append('token', token);
        }

        const checkboxes = document.querySelectorAll('input[name="partes_danificadas[]"]:checked');
        let partesSelecionadas = [];
        checkboxes.forEach(checkbox => {
            partesSelecionadas.push(checkbox.value);
        });

        formData.append('partes_danificadas', partesSelecionadas.join(','));

        try {
            const response = await fetch('../Process_form/DAT2.php', {
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
                        window.location.href = `veiculos.php?token=${token}`; // Alterado aqui
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