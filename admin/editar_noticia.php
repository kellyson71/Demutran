<?php
session_start();
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verifica e obtém o ID da notícia
if (!isset($_GET['id'])) {
    header('Location: gerenciar_noticias.php');
    exit();
}

$id = intval($_GET['id']);

// Busca a notícia existente
$sql = "SELECT * FROM noticias WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$resultado = $stmt->get_result();
$noticia = $resultado->fetch_assoc();

if (!$noticia) {
    header('Location: gerenciar_noticias.php');
    exit();
}

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $resumo = $_POST['resumo'];
    $conteudo = $_POST['conteudo'];
    $data_publicacao = $_POST['data_publicacao'];

    if (empty($titulo) || empty($resumo) || empty($conteudo)) {
        $error = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Atualiza a notícia no banco de dados
        $sql = "UPDATE noticias SET titulo = ?, resumo = ?, conteudo = ?, data_publicacao = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssi', $titulo, $resumo, $conteudo, $data_publicacao, $id);

        if ($stmt->execute()) {
            // Processa a nova imagem, se fornecida
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
                $target_dir = "../midia/noticia/" . $id;  // Alterado para incluir '../'
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $imagem_nome = basename($_FILES['imagem']['name']);
                $target_file = "midia/noticia/" . $id . '/' . $imagem_nome;  // Caminho para salvar no banco
                $full_path = "../" . $target_file;  // Caminho completo para mover o arquivo

                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $full_path)) {
                    $sql_update = "UPDATE noticias SET imagem_url = ? WHERE id = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param('si', $target_file, $id);
                    $stmt_update->execute();
                }
            }

            header('Location: gerenciar_noticias.php');
            exit();
        } else {
            $error = "Erro ao atualizar a notícia!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notícia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/djvd4vhwlkk5pio6pmjhmqd0a0j0iwziovpy9rz7k4jvzboi/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <!-- Adicionar language pack do TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/djvd4vhwlkk5pio6pmjhmqd0a0j0iwziovpy9rz7k4jvzboi/tinymce/6/langs/pt_BR.js">
    </script>
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
                    <h2 class="text-xl font-semibold text-gray-800">Editar Notícia</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- User Profile -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center focus:outline-none">
                            <?php
                            $avatarUrl = $_SESSION['usuario_avatar'] ?? '';
                            $nome = $_SESSION['usuario_nome'];
                            $iniciais = strtoupper(mb_substr($nome, 0, 1) . mb_substr(strstr($nome, ' '), 1, 1));

                            if ($avatarUrl) {
                                echo "<img src='{$avatarUrl}' alt='Avatar' 
                                      class='w-8 h-8 rounded-full object-cover ring-2 ring-blue-500 ring-offset-2'
                                      onerror=\"this.onerror=null; this.parentNode.innerHTML='<div class=\\\'w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold ring-2 ring-blue-500 ring-offset-2\\\'>{$iniciais}</div>';\">";
                            } else {
                                echo "<div class='w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold ring-2 ring-blue-500 ring-offset-2'>
                                        {$iniciais}
                                      </div>";
                            }
                            ?>
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
                                        value="<?php echo htmlspecialchars($noticia['titulo']); ?>"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="data_publicacao"
                                        class="block text-sm font-medium text-gray-700 mb-1">Data de
                                        Publicação</label>
                                    <input type="date" id="data_publicacao" name="data_publicacao"
                                        value="<?php echo $noticia['data_publicacao']; ?>"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label for="resumo" class="block text-sm font-medium text-gray-700 mb-1">Resumo</label>
                                <textarea id="resumo" name="resumo" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($noticia['resumo']); ?></textarea>
                            </div>

                            <div>
                                <label for="conteudo"
                                    class="block text-sm font-medium text-gray-700 mb-1">Conteúdo</label>
                                <textarea id="conteudo"
                                    name="conteudo"><?php echo htmlspecialchars($noticia['conteudo']); ?></textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Imagem da Notícia</label>
                                <!-- Mostrar imagem atual -->
                                <?php if (!empty($noticia['imagem_url'])): ?>
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600 mb-2">Imagem atual:</p>
                                        <img src="<?php echo '../' . htmlspecialchars($noticia['imagem_url']); ?>"
                                            alt="Imagem atual" class="max-h-64 rounded-lg">
                                    </div>
                                <?php endif; ?>
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
                                <a href="gerenciar_noticias.php"
                                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                    Cancelar
                                </a>
                                <button type="submit" id="submit-btn"
                                    class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:opacity-90 transition-opacity">
                                    Salvar Alterações
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
        // Inicialização do TinyMCE
        tinymce.init({
            selector: '#conteudo',
            language: 'pt_BR',
            height: 500,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | image media link table | help',
            content_style: "body { font-family: 'Inter', sans-serif; }",

            // Configurações em português
            language_url: 'pt_BR',

            // Configurações de imagem
            image_title: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            images_upload_handler: function(blobInfo, progress) {
                return new Promise((resolve, reject) => {
                    let formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                    fetch('upload_image.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Erro no upload: ' + response.statusText);
                            }
                            return response.json();
                        })
                        .then(result => {
                            if (result.location) {
                                // Adicionar '../' ao início do caminho retornado
                                resolve('../' + result.location);
                            } else {
                                reject(result.error || 'Erro no upload da imagem');
                            }
                        })
                        .catch(error => {
                            reject('Erro no upload: ' + error.message);
                        });
                });
            },

            // Adicionar configurações adicionais para melhor tratamento de imagens
            image_uploadtab: true,
            images_reuse_filename: true,
            automatic_uploads: true,
            images_file_types: 'jpg,jpeg,png,gif,webp',
            max_file_size: '5mb',

            // Configurações de formato
            formats: {
                alignleft: {
                    selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                    classes: 'text-left'
                },
                aligncenter: {
                    selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                    classes: 'text-center'
                },
                alignright: {
                    selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                    classes: 'text-right'
                },
                bold: {
                    inline: 'span',
                    classes: 'font-bold'
                },
                italic: {
                    inline: 'span',
                    classes: 'italic'
                }
            },

            // Menu de contexto em português
            contextmenu: "link image table",

            // Permitir tags HTML específicas e seus atributos
            extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|style],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
        });

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