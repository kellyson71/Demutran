<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../env/config.php');

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => "Erro de conexão com o banco de dados: " . $conn->connect_error
    ]);
    exit;
}

// Recebendo o token
$token = $_POST['token'];

// Primeiro, verificar se o token já existe
$check_sql = "SELECT id FROM DAT1 WHERE token = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $token);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

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

if ($check_result->num_rows > 0) {
    // Se o token existe, fazer UPDATE
    $sql = "UPDATE DAT1 SET 
        relacao_com_veiculo=?, estrangeiro=?, tipo_documento=?, numero_documento=?, 
        pais=?, nome=?, cpf=?, profissao=?, sexo=?, data_nascimento=?, 
        email=?, celular=?, cep=?, logradouro=?, numero=?, complemento=?, 
        bairro_localidade=?, cidade=?, uf=?, data=?, horario=?, 
        cidade_acidente=?, uf_acidente=?, cep_acidente=?, logradouro_acidente=?, 
        numero_acidente=?, complemento_acidente=?, bairro_localidade_acidente=?, 
        ponto_referencia_acidente=?, condicoes_via=?, sinalizacao_horizontal_vertical=?, 
        tracado_via=?, condicoes_meteorologicas=?, tipo_acidente=?
        WHERE token=?";
} else {
    // Se o token não existe, fazer INSERT
    $sql = "INSERT INTO DAT1 (
        relacao_com_veiculo, estrangeiro, tipo_documento, numero_documento, 
        pais, nome, cpf, profissao, sexo, data_nascimento, email, celular, 
        cep, logradouro, numero, complemento, bairro_localidade, cidade, uf, 
        data, horario, cidade_acidente, uf_acidente, cep_acidente, logradouro_acidente, 
        numero_acidente, complemento_acidente, bairro_localidade_acidente, 
        ponto_referencia_acidente, condicoes_via, sinalizacao_horizontal_vertical, 
        tracado_via, condicoes_meteorologicas, tipo_acidente, token
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
}

$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bind_param(
    "sisssssssssssssssssssssssssssssssss",
    $relacao_com_veiculo, $estrangeiro, $tipo_documento, $numero_documento, 
    $pais, $nome, $cpf, $profissao, $sexo, $data_nascimento, $email, $celular, 
    $cep, $logradouro, $numero, $complemento, $bairro_localidade, $cidade, $uf, 
    $data, $horario, $cidade_acidente, $uf_acidente, $cep_acidente, $logradouro_acidente, 
    $numero_acidente, $complemento_acidente, $bairro_localidade_acidente, 
    $ponto_referencia_acidente, $condicoes_via, $sinalizacao_horizontal_vertical, 
    $tracado_via, $condicoes_meteorologicas, $tipo_acidente, $token
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "Dados " . ($check_result->num_rows > 0 ? "atualizados" : "inseridos") . " com sucesso!"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "Erro ao " . ($check_result->num_rows > 0 ? "atualizar" : "inserir") . " dados: " . $stmt->error
    ]);
}

$check_stmt->close();
$stmt->close();
$conn->close();
?>