<?php
include '../env/config.php';

// Cria a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura os dados do formulário
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $assunto = $_POST['assunto'];
    $mensagem = isset($_POST['mensagem']) ? $_POST['mensagem'] : null;

    // Prepara a query de inserção
    $sql = "INSERT INTO sac (nome, telefone, email, assunto, mensagem) VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro na preparação da declaração: " . $conn->error);
    }

    $stmt->bind_param("sssss", $nome, $telefone, $email, $assunto, $mensagem);

    if ($stmt->execute()) {
        echo "Mensagem enviada com sucesso!";
    } else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>