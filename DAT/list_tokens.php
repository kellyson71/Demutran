<?php
// Incluir arquivo de configuração
include 'scr/config.php';

// Função para pegar os nomes das colunas de uma tabela
function getTableColumns($conn, $tableName) {
    $columns = [];
    $sql = "SHOW COLUMNS FROM $tableName";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }
    return $columns;
}

// Função para exibir os dados em uma tabela HTML
function displayTable($conn, $tableName) {
    // Pegar colunas
    $columns = getTableColumns($conn, $tableName);
    
    // Query para selecionar todos os dados da tabela
    $sql = "SELECT * FROM $tableName";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Adicionar um div para rolagem horizontal
        echo '<div class="table-responsive" style="overflow-x: auto;">';
        echo '<table class="table-auto w-full border-collapse my-4">';
        echo '<thead>';
        echo '<tr class="bg-blue-200 text-blue-900">';

        // Criar cabeçalho da tabela dinamicamente
        foreach ($columns as $column) {
            echo '<th class="px-4 py-2 border-b-2 border-blue-400 text-left">' . htmlspecialchars($column) . '</th>';
        }

        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Loop pelos resultados da tabela
        while ($row = $result->fetch_assoc()) {
            echo '<tr class="hover:bg-blue-50">';
            foreach ($columns as $column) {
                echo '<td class="border px-4 py-2">' . htmlspecialchars($row[$column] ?? '') . '</td>';
            }
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // Fechar o div de rolagem
    } else {
        echo '<p class="text-center text-red-500">Nenhum dado encontrado na tabela ' . htmlspecialchars($tableName) . '.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Dados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto my-10 p-6 max-w-6xl bg-white shadow-lg rounded-lg">
        <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Lista de Tokens e Dados DAT1</h1>

        <!-- Navegação para as abas -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tokens-tab" data-bs-toggle="tab" data-bs-target="#tokens"
                    type="button" role="tab" aria-controls="tokens" aria-selected="true">Tokens</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dat1-tab" data-bs-toggle="tab" data-bs-target="#dat1" type="button"
                    role="tab" aria-controls="dat1" aria-selected="false">Dados DAT1</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dat2-tab" data-bs-toggle="tab" data-bs-target="#dat2" type="button"
                    role="tab" aria-controls="dat2" aria-selected="false">Dados DAT2</button>
            </li>
        </ul>

        <!-- Conteúdo das abas -->
        <div class="tab-content" id="myTabContent">
            <!-- Aba Tokens -->
            <div class="tab-pane fade show active" id="tokens" role="tabpanel" aria-labelledby="tokens-tab">
                <?php displayTable($conn, 'tokens'); ?>
            </div>

            <!-- Aba Dados DAT1 -->
            <div class="tab-pane fade" id="dat1" role="tabpanel" aria-labelledby="dat1-tab">
                <?php displayTable($conn, 'DAT1'); ?>
            </div>
            <div class="tab-pane fade" id="dat2" role="tabpanel" aria-labelledby="dat2-tab">
                <?php displayTable($conn, 'DAT2'); ?>
            </div>
        </div>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>