<?php
require_once('../../env/config.php');

header('Content-Type: application/json');

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
    // Verificar o método da requisição
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido. Use POST para enviar dados.', 405);
    }

    // Verifica se o token foi fornecido
    if (!isset($_POST['token']) || empty($_POST['token'])) {
        throw new Exception('Token não fornecido', 400);
    }

    $token = $_POST['token'];

    // Campos obrigatórios
    $camposObrigatorios = ['situacao_veiculo', 'placa', 'tipo_veiculo', 'marca_modelo'];
    $validacao = validarDados($_POST, $camposObrigatorios);
    if (!$validacao["valido"]) {
        throw new Exception("Campo obrigatório não preenchido: " . $validacao["campo"], 400);
    }

    // Primeiro, verifica se o token existe no formularios_dat_central
    $checkToken = $conn->prepare("SELECT id FROM formularios_dat_central WHERE token = ?");
    if (!$checkToken) {
        throw new Exception('Erro ao preparar consulta: ' . $conn->error, 500);
    }

    $checkToken->bind_param("s", $token);
    if (!$checkToken->execute()) {
        throw new Exception('Erro ao executar consulta: ' . $checkToken->error, 500);
    }

    $result = $checkToken->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Token inválido ou formulário não encontrado', 404);
    }
    $checkToken->close();

    // Verificar se o registro já existe
    $checkExisting = $conn->prepare("SELECT id FROM DAT2 WHERE token = ?");
    if (!$checkExisting) {
        throw new Exception('Erro ao preparar consulta: ' . $conn->error, 500);
    }

    $checkExisting->bind_param("s", $token);
    if (!$checkExisting->execute()) {
        throw new Exception('Erro ao executar consulta: ' . $checkExisting->error, 500);
    }

    $existingResult = $checkExisting->get_result();
    $isUpdate = $existingResult->num_rows > 0;
    $checkExisting->close();

    // SQL para inserção ou atualização
    if ($isUpdate) {
        $sql = "UPDATE DAT2 SET 
                situacao_veiculo=?, placa=?, renavam=?, tipo_veiculo=?, chassi=?, 
                uf_veiculo=?, cor_veiculo=?, marca_modelo=?, ano_modelo=?, ano_fabricacao=?, 
                categoria=?, segurado=?, seguradora=?, veiculo_articulado=?, manobra_acidente=?, 
                nao_habilitado=?, numero_registro=?, uf_cnh=?, categoria_cnh=?, data_1habilitacao=?, 
                validade_cnh=?, estrangeiro_condutor=?, tipo_documento_condutor=?, 
                numero_documento_condutor=?, pais_documento_condutor=?, nome_condutor=?, 
                cpf_condutor=?, sexo_condutor=?, nascimento_condutor=?, email_condutor=?, 
                celular_condutor=?, cep_condutor=?, logradouro_condutor=?, numero_condutor=?, 
                complemento_condutor=?, bairro_condutor=?, cidade_condutor=?, uf_condutor=?, 
                danos_sistema_seguranca=?, partes_danificadas=?, danos_carga=?, numero_notas=?, 
                tipo_mercadoria=?, valor_mercadoria=?, extensao_danos=?, tem_seguro_carga=?, 
                seguradora_carga=? WHERE token=?";
    } else {
        $sql = "INSERT INTO DAT2 (
                situacao_veiculo, placa, renavam, tipo_veiculo, chassi, 
                uf_veiculo, cor_veiculo, marca_modelo, ano_modelo, ano_fabricacao, 
                categoria, segurado, seguradora, veiculo_articulado, manobra_acidente, 
                nao_habilitado, numero_registro, uf_cnh, categoria_cnh, data_1habilitacao, 
                validade_cnh, estrangeiro_condutor, tipo_documento_condutor, 
                numero_documento_condutor, pais_documento_condutor, nome_condutor, 
                cpf_condutor, sexo_condutor, nascimento_condutor, email_condutor, 
                celular_condutor, cep_condutor, logradouro_condutor, numero_condutor, 
                complemento_condutor, bairro_condutor, cidade_condutor, uf_condutor, 
                danos_sistema_seguranca, partes_danificadas, danos_carga, numero_notas, 
                tipo_mercadoria, valor_mercadoria, extensao_danos, tem_seguro_carga, 
                seguradora_carga, token
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    }

    // Prepara a inserção/atualização dos dados
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar consulta: ' . $conn->error, 500);
    }

    // Array para armazenar todas as variáveis que serão vinculadas
    $params = array();
    $types = '';

    // Adiciona cada parâmetro ao array e seu tipo correspondente à string
    $params[] = &$_POST['situacao_veiculo'];
    $params[] = &$_POST['placa'];
    $params[] = &$_POST['renavam'];
    $params[] = &$_POST['tipo_veiculo'];
    $params[] = &$_POST['chassi'];
    $params[] = &$_POST['uf_veiculo'];
    $params[] = &$_POST['cor_veiculo'];
    $params[] = &$_POST['marca_modelo'];
    $types .= str_repeat('s', 8);

    // Anos (inteiros)
    $anoModelo = empty($_POST['ano_modelo']) ? null : intval($_POST['ano_modelo']);
    $anoFabricacao = empty($_POST['ano_fabricacao']) ? null : intval($_POST['ano_fabricacao']);
    $params[] = &$anoModelo;
    $params[] = &$anoFabricacao;
    $types .= 'ii';

    // Demais campos do veículo
    $params[] = &$_POST['categoria'];
    $params[] = &$_POST['segurado'];
    $params[] = &$_POST['seguradora'];
    $params[] = &$_POST['veiculo_articulado'];
    $params[] = &$_POST['manobra_acidente'];
    $types .= str_repeat('s', 5);

    // Campos booleanos e relacionados à CNH
    $naoHabilitado = isset($_POST['nao_habilitado']) ? 1 : 0;
    $params[] = &$naoHabilitado;
    $types .= 'i';

    $params[] = &$_POST['numero_registro'];
    $params[] = &$_POST['uf_cnh'];
    $params[] = &$_POST['categoria_cnh'];
    $params[] = &$_POST['data_1habilitacao'];
    $params[] = &$_POST['validade_cnh'];
    $types .= str_repeat('s', 5);

    // Campo booleano estrangeiro
    $estrangeiroCondutor = isset($_POST['estrangeiro-condutor']) ? 1 : 0;
    $params[] = &$estrangeiroCondutor;
    $types .= 'i';

    // Campos do condutor
    $params[] = &$_POST['tipo_documento_condutor'];
    $params[] = &$_POST['numero_documento_condutor'];
    $params[] = &$_POST['pais_documento_condutor'];
    $params[] = &$_POST['nome_condutor'];
    $params[] = &$_POST['cpf_condutor'];
    $params[] = &$_POST['sexo_condutor'];
    $params[] = &$_POST['nascimento_condutor'];
    $params[] = &$_POST['email_condutor'];
    $params[] = &$_POST['celular_condutor'];
    $params[] = &$_POST['cep_condutor'];
    $params[] = &$_POST['logradouro_condutor'];
    $params[] = &$_POST['numero_condutor'];
    $params[] = &$_POST['complemento_condutor'];
    $params[] = &$_POST['bairro_condutor'];
    $params[] = &$_POST['cidade_condutor'];
    $params[] = &$_POST['uf_condutor'];
    $types .= str_repeat('s', 16);

    // Campos de danos
    $danosSistemaSeguranca = isset($_POST['danos-sistema-seguranca']) ? 1 : 0;
    $params[] = &$danosSistemaSeguranca;
    $types .= 'i';

    $partesDanificadas = empty($_POST['partes_danificadas']) ? '' : $_POST['partes_danificadas'];
    $params[] = &$partesDanificadas;
    $types .= 's';

    // Campos de carga
    $danosCarga = isset($_POST['danos_carga']) ? 1 : 0;
    $params[] = &$danosCarga;
    $types .= 'i';

    $params[] = &$_POST['numero_notas'];
    $params[] = &$_POST['tipo_mercadoria'];
    $params[] = &$_POST['valor_mercadoria'];
    $params[] = &$_POST['extensao_danos'];
    $types .= str_repeat('s', 4);

    $temSeguroCarga = isset($_POST['tem_seguro_carga']) ? 1 : 0;
    $params[] = &$temSeguroCarga;
    $types .= 'i';

    $params[] = &$_POST['seguradora_carga'];
    $types .= 's';

    // Adicionar token onde necessário
    if (!$isUpdate) {
        // Para INSERT, adicionar token no final
        $params[] = &$token;
        $types .= 's';
    } else {
        // Para UPDATE, adicionar token para WHERE
        $params[] = &$token;
        $types .= 's';
    }

    // Verificar se o número de parâmetros corresponde com a operação
    $expectedParamsCount = substr_count($sql, '?');
    if (count($params) != $expectedParamsCount) {
        throw new Exception('Erro: O número de parâmetros (' . count($params) . ') não corresponde ao número esperado (' . $expectedParamsCount . ')', 500);
    }

    // Preparar o array para bind_param
    $bindParams = array();
    $bindParams[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bindParams[] = &$params[$i];
    }

    // Faz o bind_param usando array de parâmetros
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);

    if (!$stmt->execute()) {
        throw new Exception('Erro ao salvar os dados: ' . $stmt->error, 500);
    }

    // Verificar se houve alterações
    if ($stmt->affected_rows <= 0 && !$isUpdate) {
        throw new Exception('Nenhum dado foi inserido', 500);
    }

    // Atualiza o status de preenchimento no formularios_dat_central
    $updateStatus = $conn->prepare("UPDATE formularios_dat_central SET preenchimento_status = 'Em Andamento', ultima_atualizacao = CURRENT_TIMESTAMP WHERE token = ?");
    if (!$updateStatus) {
        throw new Exception('Erro ao preparar atualização de status: ' . $conn->error, 500);
    }

    $updateStatus->bind_param("s", $token);
    if (!$updateStatus->execute()) {
        throw new Exception('Erro ao atualizar status: ' . $updateStatus->error, 500);
    }
    $updateStatus->close();

    echo json_encode([
        'success' => true,
        'message' => 'Dados do veículo e condutor ' . ($isUpdate ? 'atualizados' : 'salvos') . ' com sucesso',
        'token' => $token,
        'affected_rows' => $stmt->affected_rows
    ]);
} catch (Exception $e) {
    // Log do erro para facilitar a depuração
    error_log("Erro DAT2: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} finally {
    // Fechar as conexões abertas
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>