<?php

/**
 * Processamento do formulário dat1
 * Este arquivo é incluído diretamente pelo dados_gerais.php
 */

// Função para registrar logs
function logDAT1Processing($message, $isError = false)
{
    $logDir = __DIR__ . '/../logs';

    // Criar diretório de logs se não existir
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/dat1_processing.log';
    $timestamp = date('Y-m-d H:i:s');
    $logLevel = $isError ? 'ERROR' : 'INFO';
    $tokenInfo = isset($GLOBALS['token']) ? "Token: " . $GLOBALS['token'] : "Token não disponível";
    $logMessage = "[$timestamp] [$logLevel] [$tokenInfo] $message" . PHP_EOL;

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Impedir acesso direto ao arquivo
if (!isset($token) || !isset($_POST) || basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Acesso não autorizado.');
}

// Verificar conexão com o banco de dados
if (!$conn || $conn->connect_error) {
    $message = "Erro na conexão com o banco de dados: " . ($conn ? $conn->connect_error : "Conexão não estabelecida");
    $isError = true;
    logDAT1Processing($message, true);
    return;
}

// Verificação do token em formularios_dat_central
$check_central_sql = "SELECT id FROM formularios_dat_central WHERE token = ?";
$check_central_stmt = $conn->prepare($check_central_sql);

if (!$check_central_stmt) {
    $message = "Erro na preparação da consulta: " . $conn->error;
    $isError = true;
    logDAT1Processing($message, true);
    return;
}

$check_central_stmt->bind_param("s", $token);
$check_central_stmt->execute();
$check_central_result = $check_central_stmt->get_result();

if (!$check_central_result || $check_central_result->num_rows === 0) {
    $message = "Token inválido ou formulário não encontrado!";
    $isError = true;
    logDAT1Processing($message, true);
    $check_central_stmt->close();
    return;
}
$check_central_stmt->close();

// Verificar se o token já existe na tabela dat1
$check_sql = "SELECT id FROM dat1 WHERE token = ?";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    $message = "Erro na preparação da consulta: " . $conn->error;
    $isError = true;
    return;
}

$check_stmt->bind_param("s", $token);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$record_exists = $check_result && $check_result->num_rows > 0;
$check_stmt->close();

// Coleta e sanitização dos dados do formulário
$relacao_com_veiculo = $conn->real_escape_string($_POST['relacao_com_veiculo']);
$estrangeiro = isset($_POST['estrangeiro']) ? 1 : 0;
$tipo_documento = $estrangeiro ? $conn->real_escape_string($_POST['tipo_documento']) : '';
$numero_documento = $estrangeiro ? $conn->real_escape_string($_POST['numero_documento']) : '';
$pais = $estrangeiro ? $conn->real_escape_string($_POST['pais']) : '';
$nome = $conn->real_escape_string($_POST['nome']);
$cpf = $conn->real_escape_string($_POST['cpf']);
$profissao = isset($_POST['profissao']) ? $conn->real_escape_string($_POST['profissao']) : '';
$sexo = isset($_POST['sexo']) ? $conn->real_escape_string($_POST['sexo']) : '';
$data_nascimento = isset($_POST['data_nascimento']) ? $conn->real_escape_string($_POST['data_nascimento']) : '';
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$celular = isset($_POST['celular']) ? $conn->real_escape_string($_POST['celular']) : '';
$cep = isset($_POST['cep']) ? $conn->real_escape_string($_POST['cep']) : '';
$logradouro = isset($_POST['logradouro']) ? $conn->real_escape_string($_POST['logradouro']) : '';
$numero = isset($_POST['numero']) ? $conn->real_escape_string($_POST['numero']) : '';
$complemento = isset($_POST['complemento']) ? $conn->real_escape_string($_POST['complemento']) : '';
$bairro_localidade = isset($_POST['bairro_localidade']) ? $conn->real_escape_string($_POST['bairro_localidade']) : '';
$cidade = isset($_POST['cidade']) ? $conn->real_escape_string($_POST['cidade']) : '';
$uf = isset($_POST['uf']) ? $conn->real_escape_string($_POST['uf']) : '';
$data = isset($_POST['data']) ? $conn->real_escape_string($_POST['data']) : '';
$horario = isset($_POST['horario']) ? $conn->real_escape_string($_POST['horario']) : '';
$cidade_acidente = isset($_POST['cidade_acidente']) ? $conn->real_escape_string($_POST['cidade_acidente']) : '';
$uf_acidente = isset($_POST['uf_acidente']) ? $conn->real_escape_string($_POST['uf_acidente']) : '';
$cep_acidente = isset($_POST['cep_acidente']) ? $conn->real_escape_string($_POST['cep_acidente']) : '';
$logradouro_acidente = isset($_POST['logradouro_acidente']) ? $conn->real_escape_string($_POST['logradouro_acidente']) : '';
$numero_acidente = isset($_POST['numero_acidente']) ? $conn->real_escape_string($_POST['numero_acidente']) : '';
$complemento_acidente = isset($_POST['complemento_acidente']) ? $conn->real_escape_string($_POST['complemento_acidente']) : '';
$bairro_localidade_acidente = isset($_POST['bairro_localidade_acidente']) ? $conn->real_escape_string($_POST['bairro_localidade_acidente']) : '';
$ponto_referencia_acidente = isset($_POST['ponto_referencia_acidente']) ? $conn->real_escape_string($_POST['ponto_referencia_acidente']) : '';
$condicoes_via = isset($_POST['condicoes_via']) ? $conn->real_escape_string($_POST['condicoes_via']) : '';
$sinalizacao_horizontal_vertical = isset($_POST['sinalizacao_horizontal_vertical']) ? $conn->real_escape_string($_POST['sinalizacao_horizontal_vertical']) : '';
$tracado_via = isset($_POST['tracado_via']) ? $conn->real_escape_string($_POST['tracado_via']) : '';
$condicoes_meteorologicas = isset($_POST['condicoes_meteorologicas']) ? $conn->real_escape_string($_POST['condicoes_meteorologicas']) : '';
$tipo_acidente = isset($_POST['tipo_acidente']) ? $conn->real_escape_string($_POST['tipo_acidente']) : '';

// Validação básica de dados obrigatórios
if (empty($nome) || empty($cpf) || empty($relacao_com_veiculo)) {
    $message = "Por favor, preencha os campos obrigatórios (Nome, CPF e Relação com o veículo).";
    $isError = true;
    logDAT1Processing("Validação falhou: campos obrigatórios não preenchidos", true);
    return;
}

// Log dos campos recebidos
$camposRecebidos = [];
$camposVazios = [];
$camposFormulario = [
    'relacao_com_veiculo',
    'estrangeiro',
    'tipo_documento',
    'numero_documento',
    'pais',
    'nome',
    'cpf',
    'profissao',
    'sexo',
    'data_nascimento',
    'email',
    'celular',
    'cep',
    'logradouro',
    'numero',
    'complemento',
    'bairro_localidade',
    'cidade',
    'uf',
    'data',
    'horario',
    'cidade_acidente',
    'uf_acidente',
    'cep_acidente',
    'logradouro_acidente',
    'numero_acidente',
    'complemento_acidente',
    'bairro_localidade_acidente',
    'ponto_referencia_acidente',
    'condicoes_via',
    'sinalizacao_horizontal_vertical',
    'tracado_via',
    'condicoes_meteorologicas',
    'tipo_acidente'
];

foreach ($camposFormulario as $campo) {
    if (isset($_POST[$campo]) && !empty($_POST[$campo])) {
        $camposRecebidos[] = $campo;
    } else {
        $camposVazios[] = $campo;
    }
}

logDAT1Processing("Campos preenchidos (" . count($camposRecebidos) . "): " . implode(", ", $camposRecebidos));
logDAT1Processing("Campos vazios (" . count($camposVazios) . "): " . implode(", ", $camposVazios));

try {
    // Preparar a consulta SQL (UPDATE ou INSERT)
    if ($record_exists) {
        $sql = "UPDATE dat1 SET 
            relacao_com_veiculo=?, estrangeiro=?, tipo_documento=?, numero_documento=?, 
            pais=?, nome=?, cpf=?, profissao=?, sexo=?, data_nascimento=?, 
            email=?, celular=?, cep=?, logradouro=?, numero=?, complemento=?, 
            bairro_localidade=?, cidade=?, uf=?, data=?, horario=?, 
            cidade_acidente=?, uf_acidente=?, cep_acidente=?, logradouro_acidente=?, 
            numero_acidente=?, complemento_acidente=?, bairro_localidade_acidente=?, 
            ponto_referencia_acidente=?, condicoes_via=?, sinalizacao_horizontal_vertical=?, 
            tracado_via=?, condicoes_meteorologicas=?, tipo_acidente=?
            WHERE token=?";
        logDAT1Processing("Atualizando registro existente para o token: $token");
    } else {
        $sql = "INSERT INTO dat1 (
            relacao_com_veiculo, estrangeiro, tipo_documento, numero_documento, 
            pais, nome, cpf, profissao, sexo, data_nascimento, email, celular, 
            cep, logradouro, numero, complemento, bairro_localidade, cidade, uf, 
            data, horario, cidade_acidente, uf_acidente, cep_acidente, logradouro_acidente, 
            numero_acidente, complemento_acidente, bairro_localidade_acidente, 
            ponto_referencia_acidente, condicoes_via, sinalizacao_horizontal_vertical, 
            tracado_via, condicoes_meteorologicas, tipo_acidente, token
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        logDAT1Processing("Inserindo novo registro com token: $token");
    }

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Erro na preparação da consulta: " . $conn->error);
    }

    $stmt->bind_param(
        "sisssssssssssssssssssssssssssssssss",
        $relacao_com_veiculo,
        $estrangeiro,
        $tipo_documento,
        $numero_documento,
        $pais,
        $nome,
        $cpf,
        $profissao,
        $sexo,
        $data_nascimento,
        $email,
        $celular,
        $cep,
        $logradouro,
        $numero,
        $complemento,
        $bairro_localidade,
        $cidade,
        $uf,
        $data,
        $horario,
        $cidade_acidente,
        $uf_acidente,
        $cep_acidente,
        $logradouro_acidente,
        $numero_acidente,
        $complemento_acidente,
        $bairro_localidade_acidente,
        $ponto_referencia_acidente,
        $condicoes_via,
        $sinalizacao_horizontal_vertical,
        $tracado_via,
        $condicoes_meteorologicas,
        $tipo_acidente,
        $token
    );

    // Executa a consulta
    $execution_result = $stmt->execute();

    if (!$execution_result) {
        throw new Exception("Erro na execução da consulta: " . $stmt->error);
    }

    // Verifica se houve alteração no banco de dados
    if ($stmt->affected_rows <= 0 && !$record_exists) {
        throw new Exception("Nenhum dado foi salvo no banco de dados.");
    }

    logDAT1Processing("Operação SQL executada com sucesso. Linhas afetadas: " . $stmt->affected_rows);

    // Atualiza o timestamp em formularios_dat_central
    $update_timestamp_sql = "UPDATE formularios_dat_central SET ultima_atualizacao = CURRENT_TIMESTAMP WHERE token = ?";
    $update_timestamp_stmt = $conn->prepare($update_timestamp_sql);

    if (!$update_timestamp_stmt) {
        throw new Exception("Erro na preparação da atualização de timestamp: " . $conn->error);
    }

    $update_timestamp_stmt->bind_param("s", $token);
    $update_timestamp_result = $update_timestamp_stmt->execute();

    if (!$update_timestamp_result) {
        throw new Exception("Erro ao atualizar timestamp: " . $update_timestamp_stmt->error);
    }

    logDAT1Processing("Timestamp atualizado com sucesso na tabela formularios_dat_central");
    $update_timestamp_stmt->close();

    // Define a mensagem de sucesso
    $message = "Dados " . ($record_exists ? "atualizados" : "inseridos") . " com sucesso!";
    $isError = false;

    logDAT1Processing("Processamento concluído com sucesso: $message");
    $stmt->close();
} catch (Exception $e) {
    $message = "Erro no processamento: " . $e->getMessage();
    $isError = true;
    logDAT1Processing("Exception: " . $e->getMessage(), true);
    logDAT1Processing("Trace: " . $e->getTraceAsString(), true);
}
