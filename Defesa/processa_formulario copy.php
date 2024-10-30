<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Registro</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap">
    <link rel="icon" href="../assets/prefeitura-logo.png">

    <style>
        /* Estilos Globais */
        body {
            background-image: url('./assets/background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: #023047;
        }
        .navbar {
            background-color: #009640; /* Cor verde ajustada */
            padding: 15px;
            display: flex;
            justify-content: space-between; /* Ajuste o espaçamento entre os itens */
            align-items: center;
        }

        .logo {
            width: 70px; /* Largura ajustada conforme sua preferência */
            height: auto;
        }

        .navbar h1 {
            color: white;
            margin: 0; /* Removi a margem para um alinhamento mais consistente */
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        /* Formulário */
        .form-container {
            background-color: #fff;
            border-radius: 7px;
            padding: 40px;
            box-shadow: 10px 10px 40px rgba(0, 0, 0, 0.4);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .form-container h1 {
            margin: 0 0 20px;
            font-weight: 500;
            font-size: 2.3em;
        }
        .form-container p {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }
        .form-container input[type="text"] {
            padding: 15px;
            font-size: 14px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            margin-top: 5px;
            border-radius: 4px;
            transition: all linear 160ms;
            outline: none;
            width: calc(100% - 30px);
        }
        .form-container input[type="text"]:focus {
            border: 1px solid #009640;
        }
        .form-container input[type="submit"] {
            background-color: #009640;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border: none !important;
            transition: all linear 160ms;
            cursor: pointer;
            margin: 0 !important;
            padding: 15px 30px;
            border-radius: 4px;
        }
        .form-container input[type="submit"]:hover {
            transform: scale(1.05);
            background-color: #00b04b;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Alterar valor</h1>
        <img class="logo" src="../assets/prefeitura-logo.png" alt="Logo da empresa">
    </div>
    <div class="container">
        <div class="form-container">
            <h1>Atualizar Registro</h1>
        <?php
        $servername = "srv1078.hstgr.io";
        $username = "u492577848_Proto_estagio";
        $password = "Kellys0n_123";
        $dbname = "u492577848_protocolo";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Erro na conexão: " . $conn->connect_error);
        }

        // Verifica se foram recebidos os dados do formulário
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Recebe os valores do formulário
            $column = $_POST['column'];
            $id = $_POST['id'];

            // Verifica se os campos foram preenchidos
            if (!empty($column) && !empty($id)) {
                // Verifica se o campo "new_value" está definido
                if (isset($_POST['new_value'])) {
                    $new_value = $_POST['new_value'];

                    // Consulta SQL
                    $sql = "SELECT $column FROM protocolos WHERE id = $id";

                    // Executa a consulta
                    $result = $conn->query($sql);

                    // Verifica se há resultados
                    if ($result->num_rows > 0) {
                        // Exibe os resultados
                        while ($row = $result->fetch_assoc()) {
                            echo "<p>$column: " . $row[$column] . "</p>";
                        }

                        // Formulário para atualizar o valor
                        echo "<form method='post' action='atualizar.php'>";
                        echo "<input type='hidden' name='id' value='$id'>";
                        echo "<input type='hidden' name='column' value='$column'>";
                        echo "<input type='text' name='new_value' placeholder='Novo valor'>";
                        echo "<input type='submit' value='Atualizar'>";
                        echo "</form>";
                    } else {
                        echo "Nenhum resultado encontrado.";
                    }
                } else {
                    echo "Por favor, preencha o campo 'Novo valor'.";
                }
            } else {
                echo "Por favor, selecione uma coluna e um ID.";
            }
        }

        // Fecha a conexão
        $conn->close();
        ?>          
        </div>
    </div>
</body>
</html>