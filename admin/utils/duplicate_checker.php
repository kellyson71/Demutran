<?php
require_once '../../env/config.php';

function findDuplicates($conn, $offset = 0, $limit = 50)
{
    $duplicates = [];

    // Obter todas as colunas da tabela
    $columnsQuery = "SHOW COLUMNS FROM solicitacoes_demutran";
    $columnsResult = $conn->query($columnsQuery);
    $columns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $columns[] = $column['Field'];
    }
    $columnsStr = implode(', ', $columns);

    // Arrays de campos principais para verificar duplicatas
    $checkFields = [
        'cpf' => 'CPF',
        'placa' => 'Placa',
        'autoInfracao' => 'Auto de Infração',
        'gmail' => 'Email'
    ];

    foreach ($checkFields as $field => $label) {
        $sql = "SELECT $columnsStr, COUNT(*) as count 
                FROM solicitacoes_demutran 
                WHERE $field IS NOT NULL AND $field != ''
                GROUP BY $field 
                HAVING count > 1
                LIMIT $limit OFFSET $offset";

        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($mainRow = $result->fetch_assoc()) {
                // Buscar todos os registros duplicados
                $value = $mainRow[$field];
                $detailSql = "SELECT $columnsStr 
                             FROM solicitacoes_demutran 
                             WHERE $field = ?";

                $stmt = $conn->prepare($detailSql);
                $stmt->bind_param('s', $value);
                $stmt->execute();
                $detailResult = $stmt->get_result();

                $records = [];
                $differences = [];

                // Primeiro registro como base de comparação
                $baseRecord = $detailResult->fetch_assoc();
                $records[] = $baseRecord;

                // Comparar outros registros com o base
                while ($record = $detailResult->fetch_assoc()) {
                    $records[] = $record;
                    $recordDiff = [];

                    foreach ($columns as $column) {
                        if ($baseRecord[$column] !== $record[$column]) {
                            $recordDiff[$column] = [
                                'base' => $baseRecord[$column],
                                'atual' => $record[$column]
                            ];
                        }
                    }

                    $differences[] = $recordDiff;
                }

                $duplicates[$field][] = [
                    'value' => $value,
                    'records' => $records,
                    'differences' => $differences,
                    'count' => count($records),
                    'label' => $label
                ];
            }
        }
    }

    return $duplicates;
}

// Implementar carregamento progressivo
$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$limit = 50;
$offset = $page * $limit;

$duplicates = findDuplicates($conn, $offset, $limit);

// Se for uma requisição AJAX, retornar JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode($duplicates);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificador de Duplicatas - DEMUTRAN</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .comparison-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .comparison-table th {
            background: #f8f9fa;
            padding: 12px;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 2px solid #dee2e6;
        }

        .comparison-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .comparison-table tr:last-child td {
            border-bottom: none;
        }

        .diff-highlight {
            background-color: #fff3cd;
            border-radius: 4px;
            padding: 2px 6px;
        }

        .same-value {
            color: #198754;
            background-color: #e8f5e9;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .keep-record {
            padding: 8px;
            border: 2px solid transparent;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .keep-record:checked {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }

        .duplicate-group {
            margin-bottom: 2rem;
            background: #fff;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .group-header {
            padding: 1rem;
            background: linear-gradient(45deg, #f8f9fa, #ffffff);
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4">Verificador de Duplicatas</h1>

        <?php foreach ($duplicates as $field => $items): ?>
            <div class="mb-4">
                <h3 class="mb-3">
                    <i class="fas fa-copy"></i>
                    Duplicatas por <?php echo $items[0]['label']; ?>
                    <span class="badge bg-danger"><?php echo count($items); ?></span>
                </h3>

                <?php foreach ($items as $item): ?>
                    <div class="duplicate-group">
                        <div class="group-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo $items[0]['label']; ?>:</strong>
                                    <span class="ms-2 badge bg-primary"><?php echo htmlspecialchars($item['value']); ?></span>
                                    <span class="ms-2 badge bg-warning"><?php echo $item['count']; ?> registros</span>
                                </div>
                                <button class="btn btn-danger btn-sm delete-duplicates"
                                    data-group-id="group_<?php echo $item['records'][0]['id']; ?>">
                                    <i class="fas fa-trash-alt"></i> Excluir Selecionados
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <form class="duplicate-form" id="form_<?php echo $item['records'][0]['id']; ?>">
                                <table class="comparison-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px">Manter</th>
                                            <th style="width: 80px">ID</th>
                                            <th style="width: 160px">Data</th>
                                            <?php
                                            // Obter todas as colunas únicas dos registros
                                            $columns = [];
                                            foreach ($item['records'] as $record) {
                                                foreach ($record as $key => $value) {
                                                    if (!in_array($key, ['id', 'data_submissao']) && !in_array($key, $columns)) {
                                                        $columns[] = $key;
                                                    }
                                                }
                                            }
                                            foreach ($columns as $column):
                                            ?>
                                                <th><?php echo ucfirst($column); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($item['records'] as $index => $record): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="radio" name="keep_record_<?php echo $item['records'][0]['id']; ?>"
                                                        value="<?php echo $record['id']; ?>" class="keep-record"
                                                        <?php echo $index === 0 ? 'checked' : ''; ?>>
                                                </td>
                                                <td>#<?php echo $record['id']; ?></td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y H:i', strtotime($record['data_submissao'])); ?>
                                                    </small>
                                                </td>
                                                <?php foreach ($columns as $column): ?>
                                                    <td>
                                                        <?php
                                                        $value = $record[$column] ?? '';
                                                        $isDifferent = false;

                                                        if ($index > 0) {
                                                            $baseValue = $item['records'][0][$column] ?? '';
                                                            $isDifferent = $baseValue !== $value;
                                                        }

                                                        if ($isDifferent) {
                                                            echo "<span class='diff-highlight'>" . htmlspecialchars($value) . "</span>";
                                                        } else {
                                                            echo "<span class='same-value'>" . htmlspecialchars($value) . "</span>";
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- No final do arquivo, antes do </body> -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-duplicates').forEach(button => {
                button.addEventListener('click', async function() {
                    const groupId = this.dataset.groupId;
                    const form = document.querySelector(
                        `#form_${groupId.replace('group_', '')}`);
                    const keepId = form.querySelector('input[type="radio"]:checked').value;
                    const allIds = Array.from(form.querySelectorAll('input[type="radio"]'))
                        .map(radio => radio.value);
                    const idsToDelete = allIds.filter(id => id !== keepId);

                    if (confirm(
                            `Tem certeza que deseja manter o registro #${keepId} e mover os demais para a lixeira?`
                        )) {
                        try {
                            const response = await fetch('delete_duplicates.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    keep_id: keepId,
                                    delete_ids: idsToDelete
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                // Remover o grupo da interface após exclusão bem-sucedida
                                const group = this.closest('.duplicate-group');
                                group.remove();

                                // Mostrar mensagem de sucesso
                                alert(data.message);
                            } else {
                                // Mostrar mensagem de erro
                                alert(data.message);
                                if (data.errors) {
                                    console.error('Erros detalhados:', data.errors);
                                }
                            }
                        } catch (error) {
                            console.error('Erro na requisição:', error);
                            alert(
                                'Erro ao processar a requisição. Verifique o console para mais detalhes.');
                        }
                    }
                });
            });

            // Highlight da linha quando selecionar o radio
            document.querySelectorAll('.keep-record').forEach(radio => {
                radio.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const tbody = row.closest('tbody');

                    tbody.querySelectorAll('tr').forEach(tr => {
                        tr.style.backgroundColor = '';
                    });

                    if (this.checked) {
                        row.style.backgroundColor = '#e7f1ff';
                    }
                });
            });
        });
    </script>
</body>

</html>