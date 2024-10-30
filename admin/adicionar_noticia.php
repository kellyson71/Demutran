<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $resumo = $_POST['resumo'];
    $conteudo = $_POST['conteudo'];
    $data_publicacao = $_POST['data_publicacao'];

    // Inserir notícia no banco de dados para gerar o ID
    $sql = "INSERT INTO noticias (titulo, resumo, conteudo, data_publicacao) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $titulo, $resumo, $conteudo, $data_publicacao);

    if ($stmt->execute()) {
        $noticia_id = $stmt->insert_id; // Pegando o ID da notícia recém criada

        // Criar diretório com o nome do ID da notícia
        $target_dir = "./midia/" . $noticia_id;
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Criando o diretório com permissão
        }

        // Verificar e mover o arquivo de imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
            $imagem_nome = basename($_FILES['imagem']['name']);
            $target_file = $target_dir . '/' . $imagem_nome;
            move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file);

            // Atualizar URL da imagem no banco de dados
            $sql_update = "UPDATE noticias SET imagem_url = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('si', $target_file, $noticia_id);
            $stmt_update->execute();
        }

        header('Location: gerenciar_noticias.php');
        exit();
    } else {
        $error = "Erro ao adicionar a notícia!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Notícia</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font - Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        /* Estilo para a área de drag-and-drop */
        .dropzone {
            border: 2px dashed #3b82f6;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .dropzone:hover {
            background-color: #ebf4ff;
        }
        /* Imagem de pré-visualização */
        #preview {
            max-width: 100%;
            max-height: 300px;
            margin-top: 20px;
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Adicionar Notícia</h1>
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
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Adicionar Notícia</h2>

            <!-- Formulário de Adição -->
            <form method="POST" action="" enctype="multipart/form-data">
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="mb-6">
                    <label for="titulo" class="block text-gray-700">Título</label>
                    <input type="text" id="titulo" name="titulo" class="w-full p-3 border rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="mb-6">
                    <label for="resumo" class="block text-gray-700">Resumo</label>
                    <textarea id="resumo" name="resumo" class="w-full p-3 border rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500" rows="4" required></textarea>
                </div>

                <div class="mb-6">
                    <label for="conteudo" class="block text-gray-700">Conteúdo</label>
                    <textarea id="conteudo" name="conteudo" class="w-full p-3 border rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500" rows="8" required></textarea>
                </div>

                <!-- Campo de Upload de Imagem com Drag-and-Drop -->
                <div class="mb-6">
                    <label for="imagem" class="block text-gray-700">Imagem da Notícia</label>
                    <div id="dropzone" class="dropzone rounded-md">
                        Arraste e solte a imagem aqui ou clique para selecionar
                        <input type="file" id="imagem" name="imagem" class="hidden" accept="image/*" onchange="previewImage(event)">
                    </div>
                    <img id="preview" alt="Pré-visualização da imagem">
                </div>

                <div class="mb-6">
                    <label for="data_publicacao" class="block text-gray-700">Data de Publicação</label>
                    <input type="date" id="data_publicacao" name="data_publicacao" class="w-full p-3 border rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Botão Adicionar -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Adicionar Notícia
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script para exibir pré-visualização da imagem -->
    <script>
        // Função para exibir a imagem de pré-visualização
        function previewImage(event) {
            const input = event.target;
            const reader = new FileReader();
            reader.onload = function() {
                const preview = document.getElementById('preview');
                preview.src = reader.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }

        // Acessando o campo oculto de input ao clicar na área de drag-and-drop
        document.getElementById('dropzone').addEventListener('click', function() {
            document.getElementById('imagem').click();
        });
    </script>

</body>
</html>
