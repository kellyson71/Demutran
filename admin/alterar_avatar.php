<?php
session_start();
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Alterar Avatar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-md mx-auto my-10 p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-6">Alterar Avatar</h1>

        <!-- Preview do Avatar -->
        <div class="mb-6 text-center">
            <?php
            $userId = $_SESSION['usuario_id'];
            $avatarUrl = $_SESSION['usuario_avatar'] ?? '';
            $nome = $_SESSION['usuario_nome'];
            $iniciais = strtoupper(mb_substr($nome, 0, 1) . mb_substr(strstr($nome, ' '), 1, 1));
            
            if ($avatarUrl) {
                echo "<img id='avatarPreview' src='{$avatarUrl}?v=" . time() . "' 
                      alt='Avatar' class='w-32 h-32 rounded-full mx-auto object-cover ring-4 ring-blue-500 ring-offset-4'
                      onerror=\"this.onerror=null; this.parentNode.innerHTML='<div id=\\\'avatarPreview\\\' class=\\\'w-32 h-32 rounded-full bg-blue-500 mx-auto flex items-center justify-center text-white text-4xl font-bold ring-4 ring-blue-500 ring-offset-4\\\'>{$iniciais}</div>';\">";
            } else {
                echo "<div id='avatarPreview' class='w-32 h-32 rounded-full bg-blue-500 mx-auto flex items-center justify-center text-white text-4xl font-bold ring-4 ring-blue-500 ring-offset-4'>
                        {$iniciais}
                      </div>";
            }
            ?>
        </div>

        <!-- Formulário de Upload -->
        <form id="avatarForm" class="space-y-4">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden">
                <label for="avatar" class="cursor-pointer">
                    <span class="text-blue-500 hover:text-blue-700">Clique para selecionar</span>
                    ou arraste uma imagem aqui
                </label>
            </div>

            <div class="text-sm text-gray-500">
                <p>Formatos aceitos: JPG, PNG, GIF</p>
                <p>Tamanho máximo: 5MB</p>
                <p>Dimensões máximas: 500x500 pixels</p>
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">
                Salvar Avatar
            </button>
        </form>

        <!-- Mensagem de Status -->
        <div id="statusMessage" class="mt-4 hidden"></div>
    </div>

    <script>
    const form = document.getElementById('avatarForm');
    const fileInput = document.getElementById('avatar');
    const preview = document.getElementById('avatarPreview');
    const status = document.getElementById('statusMessage');

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className =
                    'w-32 h-32 rounded-full mx-auto object-cover ring-4 ring-blue-500 ring-offset-4';
                img.id = 'avatarPreview';

                const oldPreview = document.getElementById('avatarPreview');
                oldPreview.parentNode.replaceChild(img, oldPreview);
            }
            reader.readAsDataURL(file);
        }
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Verificar se há arquivo selecionado
        if (!fileInput.files || !fileInput.files[0]) {
            status.classList.remove('hidden');
            status.classList.add('text-red-500');
            status.textContent = 'Por favor, selecione uma imagem.';
            return;
        }

        const formData = new FormData();
        formData.append('avatar', fileInput.files[0]);

        status.classList.remove('hidden');
        status.className = 'mt-4 text-blue-500';
        status.textContent = 'Enviando...';

        try {
            const response = await fetch('upload_avatar.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            status.classList.remove('text-blue-500', 'text-red-500', 'text-green-500');
            status.classList.add(result.success ? 'text-green-500' : 'text-red-500');
            status.textContent = result.message;

            if (result.success) {
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch (error) {
            console.error('Erro:', error);
            status.classList.remove('text-blue-500');
            status.classList.add('text-red-500');
            status.textContent = 'Erro ao fazer upload. Detalhes no console.';
        }
    });
    </script>
</body>

</html>