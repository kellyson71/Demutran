<?php
session_start();
require_once '../../env/config.php';
require_once 'trash_manager.php';

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    exit(json_encode([
        'success' => false,
        'message' => 'Usuário não autenticado'
    ]));
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]));
}

// Obtém os dados do POST
$data = json_decode(file_get_contents('php://input'), true);
$keep_id = $data['keep_id'] ?? null;
$delete_ids = $data['delete_ids'] ?? [];

if (!$keep_id || empty($delete_ids)) {
    exit(json_encode([
        'success' => false,
        'message' => 'Dados inválidos para processamento'
    ]));
}

// Inicializa o gerenciador de lixeira
$trash = new TrashManager($conn, $_SESSION['user_id']);

try {
    $conn->begin_transaction();

    $successCount = 0;
    $errors = [];

    // Move cada registro duplicado para a lixeira
    foreach ($delete_ids as $id) {
        try {
            if ($trash->moveToTrash('solicitacoes_demutran', $id)) {
                $successCount++;
            }
        } catch (Exception $e) {
            $errors[] = "Erro ao processar ID {$id}: " . $e->getMessage();
        }
    }

    if (empty($errors)) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => "Sucesso! {$successCount} registros movidos para a lixeira",
            'kept_id' => $keep_id,
            'deleted_count' => $successCount,
            'deleted_ids' => $delete_ids
        ]);
    } else {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Alguns erros ocorreram durante o processo',
            'errors' => $errors
        ]);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar exclusão: ' . $e->getMessage()
    ]);
}
