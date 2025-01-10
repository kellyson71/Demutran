<?php
require_once 'env/config.php';

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
} else {
    echo "<h2>Conexão estabelecida com sucesso!</h2>";

    // Buscar todas as tabelas
    $sql = "SHOW TABLES";
    $result = $conn->query($sql);

    if ($result) {
        echo "<h3>Tabelas encontradas:</h3>";

        while ($row = $result->fetch_array()) {
            $table_name = $row[0];
            echo "<hr><strong>Tabela: {$table_name}</strong><br>";

            // Buscar estrutura de cada tabela
            $columns = $conn->query("SHOW COLUMNS FROM {$table_name}");

            if ($columns) {
                echo "<table border='1' style='margin-left: 20px;'>";
                echo "<tr><th>Coluna</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Default</th><th>Extra</th></tr>";

                while ($col = $columns->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$col['Field']}</td>";
                    echo "<td>{$col['Type']}</td>";
                    echo "<td>{$col['Null']}</td>";
                    echo "<td>{$col['Key']}</td>";
                    echo "<td>{$col['Default']}</td>";
                    echo "<td>{$col['Extra']}</td>";
                    echo "</tr>";
                }

                echo "</table><br>";
                $columns->free();
            }
        }
        $result->free();
    } else {
        echo "Erro ao listar tabelas: " . $conn->error;
    }
}

$conn->close();
