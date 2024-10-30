<?php
// Captura a mensagem de erro da URL
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Erro desconhecido';

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro</title>
</head>

<body>
    <h1>Ocorreu um erro</h1>
    <p><?php echo $message; ?></p>
    <a href="index.php">Voltar à página inicial</a>
</body>

</html>