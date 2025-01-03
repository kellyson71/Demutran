<?php
require_once('../../env/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Verifica se o token foi fornecido
    if (!isset($_POST['token']) || empty($_POST['token'])) {
        throw new Exception('Token não fornecido');
    }

    $token = $_POST['token'];

    // Primeiro, verifica se o token existe no formularios_dat_central
    $checkToken = $conn->prepare("SELECT id FROM formularios_dat_central WHERE token = ?");
    $checkToken->bind_param("s", $token);
    $checkToken->execute();
    $result = $checkToken->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Token inválido ou formulário não encontrado');
    }

    // Prepara a inserção dos dados na tabela DAT2
    $stmt = $conn->prepare("INSERT INTO DAT2 (
        token, situacao_veiculo, placa, renavam, tipo_veiculo, chassi, 
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
        seguradora_carga
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
              ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
              ?, ?, ?, ?)");

    // Array para armazenar todas as variáveis que serão vinculadas
    $params = array();
    $types = '';

    // Adiciona cada parâmetro ao array e seu tipo correspondente à string
    $params[] = &$token;
    $types .= 's';

    // Variáveis do veículo
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

    // Faz o bind_param usando array de parâmetros
    call_user_func_array(array($stmt, 'bind_param'), array_merge(array($types), $params));

    if ($stmt->execute()) {
        // Atualiza o status de preenchimento no formularios_dat_central
        $updateStatus = $conn->prepare("UPDATE formularios_dat_central SET preenchimento_status = 'Em Andamento' WHERE token = ?");
        $updateStatus->bind_param("s", $token);
        $updateStatus->execute();

        echo json_encode(['success' => true, 'message' => 'Dados do veículo e condutor salvos com sucesso']);
    } else {
        throw new Exception('Erro ao salvar os dados: ' . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>