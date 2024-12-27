<?php
// config.php

$servername = "srv1078.hstgr.io"; 
$username = "u492577848_protocolo";
$password = "WRVGAxCbrJ8wdM$"; 
$dbname = "u492577848_demutran";

// Base URL and upload directory
$base_url = "https://seusite.com/midia/";
$upload_dir = 'midia/';

/**
 * Função para converter o caminho relativo da imagem para o caminho absoluto
 */
function get_image_path($relative_path) {
    // Remove o ./ inicial se existir
    $path = ltrim($relative_path, './');
    return 'admin/' . $path;
}

// Cria a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>