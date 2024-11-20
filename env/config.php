<?php
// config.php

$servername = "srv1078.hstgr.io"; 
$username = "u492577848_protocolo";
$password = "WRVGAxCbrJ8wdM$"; 
$dbname = "u492577848_demutran";

// Cria a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>