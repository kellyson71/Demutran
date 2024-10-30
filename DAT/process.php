<?php
include 'scr/config.php';

// Preparar os dados do formulário
$columns = array();
$values = array();

foreach ($_POST as $key => $value) {
    $columns[] = $key;
    $values[] = "'" . $conn->real_escape_string($value) . "'";
}

// Inserir os dados no banco
$sql = "INSERT INTO sac (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ")";

$response = array();

if ($conn->query($sql) === TRUE) {
    // Sucesso na inserção
    $response['success'] = true;
} else {
    // Erro na inserção
    $response['success'] = false;
    $response['error'] = $conn->error;
}

$conn->close();

// Enviar a resposta em JSON
header('Content-Type: application/json');
echo json_encode($response);
?>