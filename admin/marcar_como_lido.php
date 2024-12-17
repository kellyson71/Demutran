<?php
session_start();
include '../env/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['tipo']) || !isset($data['id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$success = marcarComoLido($conn, $data['tipo'], $data['id']);
echo json_encode(['success' => $success]);
