<form action="process_form.php" method="POST">
    <label for="relacao">Relação com o veículo:</label>
    <select id="relacao" name="relacao">
        <option value="Condutor">Condutor</option>
        <option value="Corretor">Corretor</option>
        <option value="Proprietário">Proprietário</option>
        <option value="Passageiro">Passageiro</option>
        <option value="Condutor e Proprietário">Condutor e Proprietário</option>
        <option value="Terceiro Atingido">Terceiro Atingido</option>
        <option value="Estrangeiro">Estrangeiro</option>
    </select>

    <label for="estrangeiro">Estrangeiro?</label>
    <input type="checkbox" id="estrangeiro" name="estrangeiro" onchange="toggleEstrangeiroFields()">

    <div id="estrangeiro_fields" style="display: none;">
        <label for="tipo_documento">Tipo de documento:</label>
        <input type="text" id="tipo_documento" name="tipo_documento">

        <label for="numero_documento">Número do documento:</label>
        <input type="text" id="numero_documento" name="numero_documento">

        <label for="pais">País:</label>
        <input type="text" id="pais" name="pais">

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome">

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf">

        <label for="profissao">Profissão:</label>
        <input type="text" id="profissao" name="profissao">

        <label for="sexo">Sexo:</label>
        <select id="sexo" name="sexo">
            <option value="Masculino">Masculino</option>
            <option value="Feminino">Feminino</option>
            <option value="Outro">Outro</option>
        </select>

        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento">

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email">

        <label for="celular">Celular:</label>
        <input type="tel" id="celular" name="celular">

        <label for="cep">CEP:</label>
        <input type="text" id="cep" name="cep">

        <label for="logradouro">Logradouro:</label>
        <input type="text" id="logradouro" name="logradouro">

        <label for="numero">Número:</label>
        <input type="text" id="numero" name="numero">

        <label for="complemento">Complemento:</label>
        <input type="text" id="complemento" name="complemento">

        <label for="bairro">Bairro/Localidade:</label>
        <input type="text" id="bairro" name="bairro">

        <label for="cidade">Cidade:</label>
        <input type="text" id="cidade" name="cidade">

        <label for="uf">UF (Estado):</label>
        <select id="uf" name="uf">
            <option value="AC">AC</option>
            <!-- Outras opções -->
            <option value="RN">RN</option>
        </select>
    </div>

    <!-- Campos do acidente -->
    <label for="data_acidente">Data do acidente:</label>
    <input type="date" id="data_acidente" name="data_acidente">

    <label for="horario_acidente">Horário do acidente:</label>
    <input type="time" id="horario_acidente" name="horario_acidente">

    <label for="cidade_acidente">Cidade do acidente:</label>
    <input type="text" id="cidade_acidente" name="cidade_acidente" value="Pau dos Ferros">

    <label for="uf_acidente">UF do acidente:</label>
    <input type="text" id="uf_acidente" name="uf_acidente" value="RN">

    <label for="cep_acidente">CEP do acidente:</label>
    <input type="text" id="cep_acidente" name="cep_acidente">

    <label for="logradouro_acidente">Logradouro do acidente:</label>
    <input type="text" id="logradouro_acidente" name="logradouro_acidente">

    <!-- Campos restantes -->
    <!-- ... -->

    <label for="tipo_acidente">Tipo de acidente:</label>
    <select id="tipo_acidente" name="tipo_acidente">
        <option value="Atropelamento de Animal">Atropelamento de Animal</option>
        <!-- Outras opções -->
    </select>

    <button type="submit">Enviar</button>
</form>

<script>
function toggleEstrangeiroFields() {
    var estrangeiroCheckbox = document.getElementById("estrangeiro");
    var estrangeiroFields = document.getElementById("estrangeiro_fields");
    if (estrangeiroCheckbox.checked) {
        estrangeiroFields.style.display = "block";
    } else {
        estrangeiroFields.style.display = "none";
    }
}
</script>