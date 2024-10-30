<?php
include '../scr/config.php';

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error); // Se der erro, mostra uma mensagem.
}

// Recebendo os dados via POST
$relacao_com_veiculo = $_POST['relacao_com_veiculo'];
$estrangeiro = isset($_POST['estrangeiro']) ? 1 : 0; 
$tipo_documento = $_POST['tipo_documento'];
$numero_documento = $_POST['numero_documento'];
$pais = $_POST['pais'];
$nome = $_POST['nome'];
$cpf = $_POST['cpf'];
$profissao = $_POST['profissao'];
$sexo = $_POST['sexo'];
$data_nascimento = $_POST['data_nascimento'];
$email = $_POST['email'];
$celular = $_POST['celular'];
$cep = $_POST['cep'];
$logradouro = $_POST['logradouro'];
$numero = $_POST['numero'];
$complemento = $_POST['complemento'];
$bairro_localidade = $_POST['bairro_localidade'];
$cidade = $_POST['cidade'];
$uf = $_POST['uf'];
$data = $_POST['data'];
$horario = $_POST['horario'];
$cidade_acidente = $_POST['cidade_acidente'];
$uf_acidente = $_POST['uf_acidente'];
$cep_acidente = $_POST['cep_acidente'];
$logradouro_acidente = $_POST['logradouro_acidente'];
$numero_acidente = $_POST['numero_acidente'];
$complemento_acidente = $_POST['complemento_acidente'];
$bairro_localidade_acidente = $_POST['bairro_localidade_acidente'];
$ponto_referencia_acidente = $_POST['ponto_referencia_acidente'];
$condicoes_via = $_POST['condicoes_via'];
$sinalizacao_horizontal_vertical = $_POST['sinalizacao_horizontal_vertical'];
$tracado_via = $_POST['tracado_via'];
$condicoes_meteorologicas = $_POST['condicoes_meteorologicas'];
$tipo_acidente = $_POST['tipo_acidente'];

$token = $_POST['token'];

// Preparar a query de inserção
$sql = "INSERT INTO DAT1 (
    relacao_com_veiculo, estrangeiro, tipo_documento, numero_documento, pais, nome, cpf, profissao, sexo, data_nascimento, email, celular, 
    cep, logradouro, numero, complemento, bairro_localidade, cidade, uf, 
    data, horario, cidade_acidente, uf_acidente, cep_acidente, logradouro_acidente, numero_acidente, complemento_acidente, bairro_localidade_acidente, 
    ponto_referencia_acidente, condicoes_via, sinalizacao_horizontal_vertical, tracado_via, condicoes_meteorologicas, tipo_acidente, token
) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar a declaração (statement) para evitar SQL Injection
$stmt = $conn->prepare($sql);

// Ligar os parâmetros
$stmt->bind_param(
    "sisssssssssssssssssssssssssssssssss", 
    $relacao_com_veiculo, $estrangeiro, $tipo_documento, $numero_documento, $pais, $nome, $cpf, $profissao, $sexo, $data_nascimento, $email, $celular, 
    $cep, $logradouro, $numero, $complemento, $bairro_localidade, $cidade, $uf, 
    $data, $horario, $cidade_acidente, $uf_acidente, $cep_acidente, $logradouro_acidente, $numero_acidente, $complemento_acidente, $bairro_localidade_acidente, 
    $ponto_referencia_acidente, $condicoes_via, $sinalizacao_horizontal_vertical, $tracado_via, $condicoes_meteorologicas, $tipo_acidente, $token
);

// Executar a query
if ($stmt->execute()) {
    echo "Dados inseridos com sucesso!";
} else {
    echo "Erro: " . $stmt->error; // Se der erro, mostra a mensagem de erro.
}

// Fechar a conexão
$stmt->close();
$conn->close();
?>