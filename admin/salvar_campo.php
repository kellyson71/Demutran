<?php
session_start();
require_once '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Recebe os dados
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Determina a tabela baseado no tipo de formulário
$tabelas = [
    'PCD' => 'solicitacao_cartao',
    'JARI' => 'solicitacoes_demutran',
    'SAC' => 'sac',
    'Parecer' => 'Parecer',
    'DAT' => 'formularios_dat_central'
];

$tabela = $tabelas[$data['formType']] ?? null;

if (!$tabela) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipo de formulário inválido']);
    exit;
}

// Atualiza o campo
$stmt = $conn->prepare("UPDATE $tabela SET {$data['fieldName']} = ? WHERE id = ?");
$stmt->bind_param('si', $data['value'], $data['formId']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o campo']);
}

$stmt->close();
$conn->close();