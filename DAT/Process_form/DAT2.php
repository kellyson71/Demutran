<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../env/config.php'); // Corrigindo o caminho para apontar para o config.php no diretório env

// Verifica a conexão
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => "Erro de conexão com o banco de dados: " . $conn->connect_error
    ]);
    exit;
}

// Função para verificar campos de texto e atribuir "não informado" se não receber valor
function verificaTexto($valor) {
    return isset($valor) && !empty($valor) ? $valor : "não informado";
}

// Nomes das colunas da tabela
$colunas = [
    'situacao_veiculo', 'placa', 'token', 'renavam', 'tipo_veiculo', 'chassi', 'uf_veiculo', 'cor_veiculo',
    'marca_modelo', 'ano_modelo', 'ano_fabricacao', 'categoria', 'segurado', 'seguradora', 'veiculo_articulado',
    'manobra_acidente', 'nao_habilitado', 'numero_registro', 'uf_cnh', 'categoria_cnh', 'data_1habilitacao',
    'validade_cnh', 'estrangeiro_condutor', 'tipo_documento_condutor', 'numero_documento_condutor', 
    'pais_documento_condutor', 'nome_condutor', 'cpf_condutor', 'sexo_condutor', 'nascimento_condutor',
    'email_condutor', 'celular_condutor', 'cep_condutor', 'logradouro_condutor', 'numero_condutor',
    'complemento_condutor', 'bairro_condutor', 'cidade_condutor', 'uf_condutor', 'danos_sistema_seguranca', 
    'partes_danificadas', 'danos_carga', 'numero_notas', 'tipo_mercadoria', 'valor_mercadoria',
    'extensao_danos', 'tem_seguro_carga', 'seguradora_carga'
];

// Mapeia os dados do POST para os campos da tabela
$valores = [];
foreach ($colunas as $coluna) {
    $valores[] = verificaTexto($_POST[$coluna] ?? null);
}

// Buscar o ID do formulário central
$token = $_POST['token'];
$sql_form = "SELECT id FROM formularios_dat_central WHERE token = ?";
$stmt_form = $conn->prepare($sql_form);
$stmt_form->bind_param("s", $token);
$stmt_form->execute();
$result_form = $stmt_form->get_result();
$formulario_id = $result_form->fetch_object()->id;
$stmt_form->close();

// Adicionar formulario_id às colunas
$colunas[] = 'formulario_id';
$valores[] = $formulario_id;

// Preparar a query de inserção com placeholders
$placeholders = implode(', ', array_fill(0, count($colunas), '?'));
$sql = "INSERT INTO DAT2 (" . implode(', ', $colunas) . ") VALUES ($placeholders)";

// Preparar a declaração (statement) para evitar SQL Injection
$stmt = $conn->prepare($sql);

// Define os tipos de dados (assumindo que todos são strings, exceto os inteiros)
$tipos = str_repeat('s', count($valores));
$stmt->bind_param($tipos, ...$valores);

// Executar a query
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "Dados inseridos com sucesso!"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "Erro ao inserir dados: " . $stmt->error
    ]);
}

// Fechar a conexão
$stmt->close();
$conn->close();
?>