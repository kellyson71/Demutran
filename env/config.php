<?php

// Configurações do banco de dados
$servername = "srv1078.hstgr.io"; 
$username = "u492577848_protocolo";
$password = "WRVGAxCbrJ8wdM$"; 
$dbname = "u492577848_demutran";

// Base URL and upload directory
$base_url = "demutranpaudosferros.com.br";
$upload_dir = 'midia/';

/**
 * Função para converter o caminho relativo da imagem para o caminho absoluto
 */
if (!function_exists('get_image_path')) {
    function get_image_path($image_name)
    {
        return "/Demutran/assets/images/" . $image_name;
    }
}

// Cria a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>