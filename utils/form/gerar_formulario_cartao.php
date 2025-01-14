<?php
session_start();
require_once '../../env/config.php';

// Função para obter dados do formulário baseado no tipo e ID
function getDadosFormulario($conn, $tipo, $id)
{
    $tabela = '';
    $sql = '';

    switch ($tipo) {
        case 'pcd':
            $tabela = 'solicitacao_cartao';
            $sql = "SELECT * FROM $tabela WHERE id = ?";
            break;
        case 'defesa':
            $tabela = 'solicitacoes_demutran';
            $sql = "SELECT * FROM $tabela WHERE id = ?";
            break;
        case 'parecer':
            $tabela = 'Parecer';
            $sql = "SELECT * FROM $tabela WHERE id = ?";
            break;
        default:
            return null;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Obter parâmetros da URL
$tipo = $_GET['tipo'] ?? '';
$id = $_GET['id'] ?? '';

// Obter dados do formulário
$dados = getDadosFormulario($conn, $tipo, $id);

if (!$dados) {
    die('Formulário não encontrado');
}

// Incluir o arquivo apropriado baseado no tipo
switch ($tipo) {
    case 'pcd':
    case 'parecer': // Temporariamente usando o mesmo template do PCD
        include __DIR__ . '/gerar_formulario2.php';
        break;
    case 'defesa':
        require 'gerar_formulario3.php';
        break;
    default:
        die('Tipo de formulário inválido');
}

?>

<!-- <div class="logo-container">
    <img src="./image1.png" alt="Logo Esquerda" class="logo logo-left">
    <img src="./image3.png" alt="Logo Direita" class="logo logo-right">
    <div class="centered-title"> -->