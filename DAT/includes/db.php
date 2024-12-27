<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/Demutran/DAT/');
}

$servername = "localhost";
$username = "root"; // seu usuário do banco
$password = ""; // sua senha do banco
$dbname = "demutran"; // nome do seu banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8");
?>