<?php
session_start();
include '../env/config.php';
include './includes/template.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obter o ID e o tipo do formulário via GET
$id = $_GET['id'] ?? null;
$tipo = $_GET['tipo'] ?? null;

if (!$id || !$tipo) {
    header('Location: formularios.php');
    exit();
}

// Função para buscar os dados do formulário
function buscarDadosFormulario($conn, $id, $tipo) {
    $tabela = match($tipo) {
        'SAC' => 'sac',
        'JARI' => 'solicitacoes_demutran',
        'PCD' => 'solicitacao_cartao',
        'DAT' => 'DAT1',
        'Parecer' => 'Parecer',
        default => null
    };

    if (!$tabela) {
        return null;
    }

    $sql = "SELECT * FROM $tabela WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Buscar dados do formulário
$dados = buscarDadosFormulario($conn, $id, $tipo);

if (!$dados) {
    header('Location: formularios.php');
    exit();
}

// Função para formatar o nome do campo
function formatarCampo($campo) {
    $campo = str_replace('_', ' ', $campo);
    return ucfirst($campo);
}

// Campos que devem ser tratados como links
$camposLink = ['arquivo_anexo', 'documento', 'comprovante', 'midia'];

// Campos que devem ser ocultados
$camposOcultos = ['id', 'token', 'usuario_id'];

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Formulário</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
    function toggleEdit(fieldId) {
        const container = document.getElementById(`container_${fieldId}`);
        const display = document.getElementById(`display_${fieldId}`);
        const editor = document.getElementById(`editor_${fieldId}`);
        const editIcon = document.getElementById(`editIcon_${fieldId}`);

        if (display.classList.contains('hidden')) {
            // Salvando
            const novoValor = editor.value;
            if (novoValor !== display.dataset.value) {
                salvarEdicao(fieldId, novoValor);
            } else {
                cancelarEdicao(fieldId);
            }
        } else {
            // Entrando no modo de edição
            display.classList.add('hidden');
            editor.classList.remove('hidden');
            editor.focus();
            editIcon.innerHTML = '<i class="bx bx-check text-green-600"></i>';
            container.classList.add('editing');
        }
    }

    function cancelarEdicao(fieldId) {
        const display = document.getElementById(`display_${fieldId}`);
        const editor = document.getElementById(`editor_${fieldId}`);
        const editIcon = document.getElementById(`editIcon_${fieldId}`);
        const container = document.getElementById(`container_${fieldId}`);

        display.classList.remove('hidden');
        editor.classList.add('hidden');
        editIcon.innerHTML = '<i class="bx bx-pencil text-blue-600"></i>';
        container.classList.remove('editing');
    }

    function salvarEdicao(campo, novoValor) {
        const loadingIcon = `<svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>`;

        const editIcon = document.getElementById(`editIcon_${campo}`);
        editIcon.innerHTML = loadingIcon;

        fetch('editar_formulario_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: <?php echo $id; ?>,
                tipo: '<?php echo $tipo; ?>',
                campo: campo,
                novoValor: novoValor
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const display = document.getElementById(`display_${campo}`);
                display.textContent = novoValor;
                display.dataset.value = novoValor;
                cancelarEdicao(campo);
                
                // Mostrar notificação de sucesso
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-lg';
                notification.innerHTML = '<i class="bx bx-check mr-2"></i> Campo atualizado com sucesso!';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 3000);
            } else {
                throw new Error(data.message || 'Erro ao atualizar');
            }
        })
        .catch(error => {
            alert('Erro ao salvar: ' + error.message);
            cancelarEdicao(campo);
        });
    }

    function handleKeyPress(event, fieldId) {
        if (event.key === 'Enter') {
            toggleEdit(fieldId);
        } else if (event.key === 'Escape') {
            cancelarEdicao(fieldId);
        }
    }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 flex flex-col h-full">
                <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                <?php echo getSidebarHtml('formularios'); ?>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php 
            $topbarHtml = getTopbarHtml('Visualizar Formulário', $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6">
                <div class="container mx-auto px-4 py-8">
                    <!-- Cabeçalho -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">
                                    Protocolo #<?php echo $id; ?>
                                </h1>
                                <p class="text-gray-600">
                                    Tipo: <?php echo $tipo; ?>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <a href="formularios.php" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">
                                    <i class='bx bx-arrow-back'></i> Voltar
                                </a>
                                <button class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                    <i class='bx bx-edit-alt'></i> Editar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Conteúdo -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($dados as $campo => $valor): 
                                if (in_array($campo, $camposOcultos)) continue;
                                $fieldId = htmlspecialchars($campo);
                            ?>
                                <div id="container_<?php echo $fieldId; ?>" 
                                     class="border-b border-gray-200 pb-4 group hover:bg-gray-50 rounded-lg p-3 transition-colors">
                                    <div class="flex justify-between items-center">
                                        <label class="block text-sm font-medium text-gray-600 mb-1">
                                            <?php echo formatarCampo($campo); ?>
                                        </label>
                                        <?php if (!in_array($campo, $camposLink)): ?>
                                            <button onclick="toggleEdit('<?php echo $fieldId; ?>')" 
                                                    id="editIcon_<?php echo $fieldId; ?>"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-blue-100 rounded">
                                                <i class='bx bx-pencil text-blue-600'></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (in_array($campo, $camposLink) && !empty($valor)): ?>
                                        <a href="<?php echo htmlspecialchars($valor); ?>" 
                                        target="_blank"
                                        class="text-blue-500 hover:text-blue-700 flex items-center gap-2">
                                            <i class='bx bx-link-external'></i>
                                            Visualizar anexo
                                        </a>
                                    <?php else: ?>
                                        <div id="display_<?php echo $fieldId; ?>" 
                                             data-value="<?php echo htmlspecialchars($valor); ?>"
                                             class="text-gray-900">
                                            <?php echo !empty($valor) ? htmlspecialchars($valor) : 
                                                '<span class="text-gray-400">Não informado</span>'; ?>
                                        </div>
                                        <input type="text" 
                                               id="editor_<?php echo $fieldId; ?>"
                                               class="hidden w-full p-2 border rounded-md"
                                               value="<?php echo htmlspecialchars($valor); ?>"
                                               onkeydown="handleKeyPress(event, '<?php echo $fieldId; ?>')"
                                               onblur="cancelarEdicao('<?php echo $fieldId; ?>')">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Status do Formulário -->
                    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Status do Formulário</h2>
                        <div class="flex items-center gap-4">
                            <span class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-800">
                                Em processamento
                            </span>
                            <button class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                <i class='bx bx-check'></i> Marcar como concluído
                            </button>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">&copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os direitos reservados.</p>
            </footer>
        </div>
    </div>
</body>
</html>