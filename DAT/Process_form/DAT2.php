<?php
include '../scr/config.php';

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error); // Se der erro, mostra uma mensagem.
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
    echo "Dados inseridos com sucesso!";
} else {
    echo "Erro: " . $stmt->error; // Se der erro, mostra a mensagem de erro.
}

// Fechar a conexão
$stmt->close();
$conn->close();
?>