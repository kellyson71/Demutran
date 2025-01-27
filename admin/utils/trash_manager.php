<?php
require_once '../../env/config.php';

class TrashManager
{
    private $conn;
    private $user_id;

    public function __construct($conn, $user_id)
    {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    /**
     * Move um registro para a lixeira
     */
    public function moveToTrash($table_name, $record_id)
    {
        try {
            // Inicia a transação
            $this->conn->begin_transaction();

            // Busca os dados originais
            $stmt = $this->conn->prepare("SELECT * FROM $table_name WHERE id = ?");
            $stmt->bind_param('i', $record_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $original_data = $result->fetch_assoc();

            if (!$original_data) {
                throw new Exception("Registro não encontrado.");
            }

            // Insere na lixeira
            $json_data = json_encode($original_data);
            $stmt = $this->conn->prepare(
                "INSERT INTO trash_bin (original_id, table_name, data, user_id) 
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param('issi', $record_id, $table_name, $json_data, $this->user_id);
            $stmt->execute();

            // Deleta o registro original
            $stmt = $this->conn->prepare("DELETE FROM $table_name WHERE id = ?");
            $stmt->bind_param('i', $record_id);
            $stmt->execute();

            // Confirma a transação
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Restaura um registro da lixeira
     */
    public function restoreFromTrash($trash_id)
    {
        try {
            $this->conn->begin_transaction();

            // Busca os dados da lixeira
            $stmt = $this->conn->prepare("SELECT * FROM trash_bin WHERE id = ?");
            $stmt->bind_param('i', $trash_id);
            $stmt->execute();
            $trash_item = $stmt->get_result()->fetch_assoc();

            if (!$trash_item) {
                throw new Exception("Item não encontrado na lixeira.");
            }

            $original_data = json_decode($trash_item['data'], true);
            $table_name = $trash_item['table_name'];

            // Remove o ID do array de dados
            unset($original_data['id']);

            // Prepara as colunas e valores para o INSERT
            $columns = implode(', ', array_keys($original_data));
            $values = implode(', ', array_fill(0, count($original_data), '?'));
            $types = str_repeat('s', count($original_data));

            // Insere o registro de volta na tabela original
            $sql = "INSERT INTO $table_name ($columns) VALUES ($values)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...array_values($original_data));
            $stmt->execute();

            // Remove da lixeira
            $stmt = $this->conn->prepare("DELETE FROM trash_bin WHERE id = ?");
            $stmt->bind_param('i', $trash_id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Exclui permanentemente um registro da lixeira
     */
    public function permanentDelete($trash_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM trash_bin WHERE id = ?");
        $stmt->bind_param('i', $trash_id);
        return $stmt->execute();
    }

    /**
     * Lista itens na lixeira
     */
    public function listTrashItems($table_name = null, $limit = 50, $offset = 0)
    {
        $sql = "SELECT * FROM trash_bin";
        if ($table_name) {
            $sql .= " WHERE table_name = ?";
        }
        $sql .= " ORDER BY deleted_at DESC LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($sql);

        if ($table_name) {
            $stmt->bind_param('sii', $table_name, $limit, $offset);
        } else {
            $stmt->bind_param('ii', $limit, $offset);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Exemplo de uso:
/*
$trash = new TrashManager($conn, $user_id);

// Mover para lixeira
try {
    $trash->moveToTrash('solicitacoes_demutran', 123);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

// Restaurar da lixeira
try {
    $trash->restoreFromTrash(456);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

// Listar itens da lixeira
$items = $trash->listTrashItems('solicitacoes_demutran');
*/
