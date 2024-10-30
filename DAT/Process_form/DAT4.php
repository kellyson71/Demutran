<?php
include '../scr/config.php';

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error); // Se der erro, mostra uma mensagem.
}

function verificaTexto($valor) {
    return isset($valor) && !empty($valor) ? $valor : "não informado";
}

// Nomes das colunas da tabela
$colunas = [
	'token',	'patrimonio_text',	'meio_ambiente_text',	'informacoes_complementares_text'	

];

// Mapeia os dados do POST para os campos da tabela
$valores = [];
foreach ($colunas as $coluna) {
    $valores[] = verificaTexto($_POST[$coluna] ?? null);
}

// Preparar a query de inserção com placeholders
$placeholders = implode(', ', array_fill(0, count($colunas), '?'));
$sql = "INSERT INTO DAT4 (" . implode(', ', $colunas) . ") VALUES ($placeholders)";

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