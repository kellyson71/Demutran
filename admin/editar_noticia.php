<?php
session_start();
include '../env/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obtém o ID da notícia a ser editada
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Busca a notícia no banco de dados
    $sql = "SELECT * FROM noticias WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $noticia = $resultado->fetch_assoc();

    if (!$noticia) {
        echo "Notícia não encontrada.";
        exit();
    }

    // Atualiza a notícia
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = $_POST['titulo'];
        $resumo = $_POST['resumo'];
        $conteudo = $_POST['conteudo'];
        $data_publicacao = $_POST['data_publicacao'];

        $sql_update = "UPDATE noticias SET titulo = ?, resumo = ?, conteudo = ?, data_publicacao = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('ssssi', $titulo, $resumo, $conteudo, $data_publicacao, $id);

        if ($stmt_update->execute()) {
            header('Location: gerenciar_noticias.php');
            exit();
        } else {
            $error = "Erro ao atualizar a notícia!";
        }
    }
} else {
    echo "ID da notícia não fornecido.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notícia</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font - Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Roboto', sans-serif;
    }

    .input-focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
    }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Editar Notícia</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">Olá, <?php echo $_SESSION['usuario_nome']; ?></span>
                <a href="logout.php" class="text-red-600 hover:underline">Sair</a>
            </div>
        </div>
    </nav>

    <!-- Espaço para compensar o navbar fixo -->
    <div class="h-16"></div>

    <!-- Conteúdo Principal -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg p-8 max-w-3xl mx-auto">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Editar Notícia</h2>

            <!-- Formulário de Edição -->
            <form method="POST" action="">
                <?php if (isset($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo $error; ?></p>
                </div>
                <?php endif; ?>

                <div class="mb-6">
                    <label for="titulo" class="block text-gray-700">Título</label>
                    <input type="text" id="titulo" name="titulo"
                        class="w-full p-3 border rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 input-focus"
                        value="<?php echo $noticia['titulo']; ?>" required>
                </div>

                <div class="mb-6">
                    <label for="resumo" class="block text-gray-700">Resumo</label>
                    <textarea id="resumo" name="resumo"
                        class="w-full p-3 border rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 input-focus"
                        rows="4" required><?php echo $noticia['resumo']; ?></textarea>
                </div>

                <div class="mb-6">
                    <label for="conteudo" class="block text-gray-700">Conteúdo</label>
                    <textarea id="conteudo" name="conteudo"
                        class="w-full p-3 border rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 input-focus"
                        rows="8" required><?php echo $noticia['conteudo']; ?></textarea>
                </div>

                <div class="mb-6">
                    <label for="imagem_url" class="block text-gray-700">URL da Imagem</label>
                    <input type="text" id="imagem_url" name="imagem_url"
                        class="w-full p-3 border rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 input-focus"
                        value="<?php echo $noticia['imagem_url']; ?>" required>
                </div>

                <div class="mb-6">
                    <label for="data_publicacao" class="block text-gray-700">Data de Publicação</label>
                    <input type="date" id="data_publicacao" name="data_publicacao"
                        class="w-full p-3 border rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 input-focus"
                        value="<?php echo $noticia['data_publicacao']; ?>" required>
                </div>

                <!-- Botão Salvar -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>