<?php
session_start();
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $resumo = $_POST['resumo'];
    $conteudo = $_POST['conteudo'];
    $data_publicacao = $_POST['data_publicacao'];

    // Validação dos dados de entrada
    if (empty($titulo) || empty($resumo) || empty($conteudo)) {
        $error = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Inserir notícia no banco de dados para gerar o ID
        $sql = "INSERT INTO noticias (titulo, resumo, conteudo, data_publicacao) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $titulo, $resumo, $conteudo, $data_publicacao);

        if ($stmt->execute()) {
            $noticia_id = $stmt->insert_id; // Pegando o ID da notícia recém criada

            // Criar diretório com o nome do ID da notícia
            $target_dir = "../midia/noticia/" . $noticia_id;
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Criando o diretório com permissão
            }

            // Verificar e mover o arquivo de imagem
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
                $capa_filename = "capa." . $ext;
                $target_file = $target_dir . '/' . $capa_filename;
                move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file);

                // Atualizar URL da imagem no banco de dados
                $imagem_url = "midia/noticia/" . $noticia_id . "/" . $capa_filename;
                $sql_update = "UPDATE noticias SET imagem_url = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param('si', $imagem_url, $noticia_id);
                $stmt_update->execute();
            }

            // Processar imagens do conteúdo
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Suprimir avisos do HTML mal formado
            $dom->loadHTML(mb_convert_encoding($conteudo, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            $images = $dom->getElementsByTagName('img');
            $i = 1;

            foreach ($images as $img) {
                if ($img instanceof DOMElement) {
                    $img_src = $img->getAttribute('src');

                    // Se for uma imagem em base64
                    if (strpos($img_src, 'data:image/') === 0) {
                        $img_data = base64_decode(explode(',', $img_src)[1]);
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime_type = $finfo->buffer($img_data);
                        $ext = explode('/', $mime_type)[1];

                        $new_filename = "imagem" . $i . "." . $ext;
                        $img_path = $target_dir . '/' . $new_filename;
                        file_put_contents($img_path, $img_data);

                        $new_src = "midia/noticia/" . $noticia_id . "/" . $new_filename;
                        $img->setAttribute('src', $new_src);
                        $i++;
                    }
                }
            }

            // Atualizar o conteúdo com os novos caminhos das imagens
            $conteudo_atualizado = $dom->saveHTML();
            $sql_update = "UPDATE noticias SET conteudo = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('si', $conteudo_atualizado, $noticia_id);
            $stmt_update->execute();

            header('Location: gerenciar_noticias.php');
            exit();
        } else {
            $error = "Erro ao adicionar a notícia!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Notícia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Remover scripts do TinyMCE -->
    <!-- Adicionar Quill.js -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <style>
    body {
        font-family: 'Inter', sans-serif;
    }

    .dropzone {
        border: 2px dashed #4F46E5;
        background: #F5F3FF;
        transition: all 0.3s ease;
    }

    .dropzone.dragover {
        background: #EEF2FF;
        border-color: #6366F1;
    }

    .tox-tinymce {
        border-radius: 0.5rem !important;
    }

    .tox .tox-mbtn__select-label {
        font-family: 'Inter', sans-serif !important;
    }

    /* Estilos para o Quill */
    .ql-editor {
        min-height: 200px;
        font-family: 'Inter', sans-serif;
    }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Substituir navbar antiga pelo novo Wrapper com Sidebar -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 flex flex-col h-full">
                <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                <nav class="space-y-2 flex-1">
                    <a href="index.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">dashboard</span>
                        <span class="ml-3">Dashboard</span>
                    </a>
                    <a href="formularios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">assignment</span>
                        <span class="ml-3">Formulários</span>
                    </a>
                    <a href="gerenciar_noticias.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                        <span class="material-icons">article</span>
                        <span class="ml-3 font-semibold">Notícias</span>
                    </a>
                    <a href="usuarios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">people</span>
                        <span class="ml-3">Usuários</span>
                    </a>
                </nav>
                <div class="mt-6">
                    <a href="logout.php" class="flex items-center p-2 text-red-600 hover:bg-red-50 rounded">
                        <span class="material-icons">logout</span>
                        <span class="ml-3">Sair</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <button @click="open = !open" class="md:hidden focus:outline-none">
                        <span class="material-icons">menu</span>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800">Adicionar Notícia</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- User Profile -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center focus:outline-none">
                            <img src="avatar.png" alt="Avatar" class="w-8 h-8 rounded-full">
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-50">
                            <div class="p-4 border-b text-gray-700 font-bold">
                                <?php echo $_SESSION['usuario_nome']; ?>
                            </div>
                            <ul>
                                <li class="p-4 hover:bg-gray-50">
                                    <a href="perfil.php" class="block text-gray-700">Perfil</a>
                                </li>
                                <li class="p-4 hover:bg-gray-50">
                                    <a href="logout.php" class="block text-red-600">Sair</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Mover o conteúdo existente para dentro desta main -->
                <div class="container mx-auto">
                    <div class="bg-white shadow-xl rounded-2xl p-8 max-w-4xl mx-auto">
                        <?php if (isset($error)): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                            <p><?php echo $error; ?></p>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label for="titulo"
                                        class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                    <input type="text" id="titulo" name="titulo"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="data_publicacao"
                                        class="block text-sm font-medium text-gray-700 mb-1">Data de
                                        Publicação</label>
                                    <input type="date" id="data_publicacao" name="data_publicacao"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label for="resumo" class="block text-sm font-medium text-gray-700 mb-1">Resumo</label>
                                <textarea id="resumo" name="resumo" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>

                            <div>
                                <label for="editor"
                                    class="block text-sm font-medium text-gray-700 mb-1">Conteúdo</label>
                                <div id="editor"></div>
                                <input type="hidden" name="conteudo" id="conteudo">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Imagem da Notícia</label>
                                <div id="dropzone" class="dropzone rounded-lg p-8 text-center cursor-pointer">
                                    <div class="space-y-2">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                            viewBox="0 0 48 48">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="text-gray-600">
                                            Arraste e solte uma imagem ou clique para selecionar
                                        </div>
                                    </div>
                                    <input type="file" id="imagem" name="imagem" class="hidden" accept="image/*">
                                </div>
                                <div id="preview-container" class="hidden mt-4">
                                    <img id="preview" class="max-h-64 rounded-lg mx-auto">
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="history.back()"
                                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                    Cancelar
                                </button>
                                <button type="submit" id="submit-btn"
                                    class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:opacity-90 transition-opacity">
                                    Publicar Notícia
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Adicionar os scripts necessários -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="//unpkg.com/alpinejs" defer></script>

    <script>
    // Inicializar Quill
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{
                    'header': 1
                }, {
                    'header': 2
                }],
                [{
                    'list': 'ordered'
                }, {
                    'list': 'bullet'
                }],
                [{
                    'script': 'sub'
                }, {
                    'script': 'super'
                }],
                [{
                    'indent': '-1'
                }, {
                    'indent': '+1'
                }],
                [{
                    'size': ['small', false, 'large', 'huge']
                }],
                [{
                    'header': [1, 2, 3, 4, 5, 6, false]
                }],
                [{
                    'color': []
                }, {
                    'background': []
                }],
                [{
                    'align': []
                }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Digite o conteúdo da notícia aqui...'
    });

    // Atualizar o campo hidden antes do envio do formulário
    document.querySelector('form').onsubmit = function() {
        document.getElementById('conteudo').value = quill.root.innerHTML;
        return true;
    };

    // Preview da imagem
    const dropzone = document.getElementById('dropzone');
    const imageInput = document.getElementById('imagem');
    const previewContainer = document.getElementById('preview-container');
    const preview = document.getElementById('preview');

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            imageInput.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });

    dropzone.addEventListener('click', () => imageInput.click());

    imageInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            showPreview(e.target.files[0]);
        }
    });

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
    </script>

</body>

</html>