<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../env/config.php');

// Função para validar dados
function validarDados($dados, $obrigatorios = [])
{
    foreach ($obrigatorios as $campo) {
        if (!isset($dados[$campo]) || trim($dados[$campo]) === '') {
            return ["campo" => $campo, "valido" => false];
        }
    }
    return ["valido" => true];
}

try {
    // Verificar a conexão com o banco de dados
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão com o banco de dados: " . $conn->connect_error);
    }

    // Verificar se o método é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método não permitido. Use POST para enviar dados.");
    }

    // Verificar se token foi fornecido
    if (!isset($_POST['token']) || empty($_POST['token'])) {
        throw new Exception("Token não fornecido");
    }

    // Recebendo o token
    $token = $_POST['token'];

    // Campos obrigatórios
    $camposObrigatorios = ['nome', 'data', 'horario', 'cidade_acidente', 'uf_acidente'];
    $validacao = validarDados($_POST, $camposObrigatorios);
    if (!$validacao["valido"]) {
        throw new Exception("Campo obrigatório não preenchido: " . $validacao["campo"]);
    }

    // Verificar se o token existe em formularios_dat_central
    $check_central_sql = "SELECT id FROM formularios_dat_central WHERE token = ?";
    $check_central_stmt = $conn->prepare($check_central_sql);
    if (!$check_central_stmt) {
        throw new Exception("Erro ao preparar consulta: " . $conn->error);
    }

    $check_central_stmt->bind_param("s", $token);
    if (!$check_central_stmt->execute()) {
        throw new Exception("Erro ao executar consulta: " . $check_central_stmt->error);
    }

    $check_central_result = $check_central_stmt->get_result();
    if ($check_central_result->num_rows === 0) {
        throw new Exception("Token inválido ou formulário não encontrado!");
    }
    $check_central_stmt->close();

    // Verificar se o token já existe na DAT1
    $check_sql = "SELECT id FROM DAT1 WHERE token = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Erro ao preparar consulta: " . $conn->error);
    }

    $check_stmt->bind_param("s", $token);
    if (!$check_stmt->execute()) {
        throw new Exception("Erro ao executar consulta: " . $check_stmt->error);
    }

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
        $sql =
            "INSERT INTO DAT1 (
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
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . $conn->error);
    }

    // Bind parameters - Ajustando a string de tipos para 35 parâmetros
    if ($check_result->num_rows > 0) {
        $stmt->bind_param(
            "sisssssssssssssssssssssssssssssssss",  // 35 tipos (34 campos + token para WHERE)
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
    } else {
        $stmt->bind_param(
            "sisssssssssssssssssssssssssssssssss",  // 35 tipos (34 campos + token)
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
    }

    if (!$stmt->execute()) {
        throw new Exception("Erro ao " . ($check_result->num_rows > 0 ? "atualizar" : "inserir") . " dados: " . $stmt->error);
    }

    // Verificar se alguma linha foi afetada
    if ($stmt->affected_rows <= 0) {
        throw new Exception("Nenhum dado foi " . ($check_result->num_rows > 0 ? "atualizado" : "inserido"));
    }

    // Atualizar o timestamp em formularios_dat_central
    $update_timestamp_sql = "UPDATE formularios_dat_central SET ultima_atualizacao = CURRENT_TIMESTAMP WHERE token = ?";
    $update_timestamp_stmt = $conn->prepare($update_timestamp_sql);
    if (!$update_timestamp_stmt) {
        throw new Exception("Erro ao preparar atualização do timestamp: " . $conn->error);
    }

    $update_timestamp_stmt->bind_param("s", $token);
    if (!$update_timestamp_stmt->execute()) {
        throw new Exception("Erro ao atualizar timestamp: " . $update_timestamp_stmt->error);
    }
    $update_timestamp_stmt->close();

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => "Dados " . ($check_result->num_rows > 0 ? "atualizados" : "inseridos") . " com sucesso!",
        'token' => $token,
        'affected_rows' => $stmt->affected_rows
    ]);
} catch (Exception $e) {
    // Log do erro para facilitar a depuração
    error_log("Erro DAT1: " . $e->getMessage());

    // Resposta de erro
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} finally {
    // Fechar as conexões abertas
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>