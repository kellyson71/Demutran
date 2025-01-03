<?php
session_start();
include '../env/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['tipo']) || !isset($data['id'])) {
    echo json_encode(['success' => false]);
    exit;
}

function marcarComoLido($conn, $tipo, $id)
{
    $tabela = match ($tipo) {
        'SAC' => 'sac',
        'JARI' => 'solicitacoes_demutran',
        'PCD' => 'solicitacao_cartao',
        'DAT' => 'DAT1',
        'Parecer' => 'Parecer',
        default => null
    };

    if (!$tabela) return false;

    $sql = "UPDATE $tabela SET lido = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

$success = marcarComoLido($conn, $data['tipo'], $data['id']);
echo json_encode(['success' => $success]);
