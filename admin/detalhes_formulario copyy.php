<?php
session_start();
include '../env/config.php';
include './includes/template.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obter o ID e o tipo do formulário via GET
$id = $_GET['id'];
$tipo = $_GET['tipo'];

function obterUltimosFormulariosSAC($conn)
{
    $sql = "SELECT * FROM sac ORDER BY id DESC LIMIT 2";
    return $conn->query($sql);
}

function atualizarSituacaoFormulario($conn, $id, $tipo)
{
    $tabela = '';
    switch ($tipo) {
        case 'DAT':
            $tabela = 'DAT4';
            break;
        case 'SAC':
            $tabela = 'sac';
            break;
        case 'JARI':
            $tabela = 'solicitacoes_demutran';
            break;
        case 'PCD':
            $tabela = 'solicitacao_cartao';
            break;
        case 'Parecer':
            $tabela = 'Parecer';
            break;
    }

    if ($tabela) {
        $sql = "UPDATE $tabela SET situacao = 'Concluído' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

$sacFormularios = obterUltimosFormulariosSAC($conn);
$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

// Atualizar a função exibir_dados_formatados para tratar URLs
function exibir_dados_formatados($dados)
{
    $colunas_personalizadas = [
        'created_at' => 'Criado em',
        'damage_system' => 'Sistema de danos',
        'damaged_parts' => 'Partes danificadas',
        'arquivo_anexo' => 'Arquivo Anexado',
        'documento' => 'Documento',
        'comprovante' => 'Comprovante',
        'midia' => 'Mídia',
    ];

    $colunas_ocultas = ['token', 'id', 'damaged_parts'];
    $colunas_arquivo = ['arquivo_anexo', 'documento', 'comprovante', 'midia'];

    if (empty($dados)) {
        echo "<div class='bg-gray-50 p-4 rounded-lg text-gray-600 font-medium'>Dados não disponíveis.</div>";
        return;
    }

    echo "<div class='border border-gray-200 rounded-lg divide-y divide-gray-200'>";

    foreach ($dados as $coluna => $valor) {
        if (!in_array($coluna, $colunas_ocultas)) {
            $nome_coluna = isset($colunas_personalizadas[$coluna]) ? $colunas_personalizadas[$coluna] : ucfirst(str_replace('_', ' ', $coluna));

            echo "<div class='px-4 py-3 bg-white hover:bg-gray-50 transition-colors'>";

            if (in_array($coluna, $colunas_arquivo) || filter_var($valor, FILTER_VALIDATE_URL)) {
                echo "<div class='flex items-center justify-between'>";
                echo "<div class='text-gray-600 font-medium'>" . $nome_coluna . "</div>";
                echo "<a href='" . htmlspecialchars($valor) . "' target='_blank' 
                     class='inline-flex items-center gap-2 text-blue-700 hover:text-blue-800 font-medium text-sm bg-blue-50 px-3 py-1 rounded border border-blue-100'>
                     <i class='bx bx-file'></i>
                     <span>Ver arquivo</span>
                     </a>";
                echo "</div>";
            } else {
                echo "<div class='grid grid-cols-3 gap-4'>";
                echo "<div class='text-gray-600 font-medium'>" . $nome_coluna . "</div>";
                echo "<div class='col-span-2 text-gray-800'>" .
                    (!empty($valor) ? htmlspecialchars($valor) :
                        '<span class="text-gray-400 italic">Não informado</span>') .
                    "</div>";
                echo "</div>";
            }

            echo "</div>";
        }
    }

    echo "</div>";
}

// Lógica específica para cada tipo de formulário
if ($tipo == 'DAT') {
    // Consultar dados de DAT4 usando o id fornecido
    $sqlDAT4 = "SELECT * FROM DAT4 WHERE id = ?";
    $stmt4 = $conn->prepare($sqlDAT4);
    $stmt4->bind_param('i', $id);
    $stmt4->execute();
    $result4 = $stmt4->get_result();
    $dat4 = $result4->fetch_assoc();

    if (!$dat4) {
        echo "Formulário não encontrado em DAT4.";
        exit();
    }

    // Obter o token do registro em DAT4
    $token = $dat4['token'];

    // Consultar dados de DAT1 usando o token
    $sqlDAT1 = "SELECT * FROM DAT1 WHERE token = ?";
    $stmt1 = $conn->prepare($sqlDAT1);
    $stmt1->bind_param('s', $token);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $dat1 = $result1->fetch_assoc();

    // Se não encontrar, inicializar $dat1 como array vazio
    if (!$dat1) {
        $dat1 = [];
    }

    // Consultar dados de DAT2 usando o token
    $sqlDAT2 = "SELECT * FROM DAT2 WHERE token = ?";
    $stmt2 = $conn->prepare($sqlDAT2);
    $stmt2->bind_param('s', $token);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $dat2 = $result2->fetch_assoc();

    if (!$dat2) {
        $dat2 = [];
    }

    // Consultar dados de vehicles (DAT3) usando o token
    $sqlDAT3 = "SELECT * FROM vehicles WHERE token = ?";
    $stmt3 = $conn->prepare($sqlDAT3);
    $stmt3->bind_param('s', $token);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    $dat3 = [];
    while ($row = $result3->fetch_assoc()) {
        $dat3[] = $row;
    }

    // Se $dat3 estiver vazio, adicionar um array vazio para manter a consistência
    if (empty($dat3)) {
        $dat3[] = [];
    }
} elseif ($tipo == 'Parecer') { // Adicionado suporte para 'Parecer'
    $sql = "SELECT * FROM Parecer WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $formulario = $result->fetch_assoc();

    if (!$formulario) {
        echo "Formulário não encontrado.";
        exit();
    }
} else {
    // Lógica para PCD, SAC e JARI
    if ($tipo == 'PCD') {
        $sql = "SELECT * FROM solicitacao_cartao WHERE id = ?";
    } elseif ($tipo == 'SAC') {
        $sql = "SELECT * FROM sac WHERE id = ?";
    } elseif ($tipo == 'JARI') {
        $sql = "SELECT * FROM solicitacoes_demutran WHERE id = ?";
    } else {
        echo "Tipo de formulário inválido.";
        exit();
    }

    // Preparar e executar a consulta para PCD, SAC, ou JARI
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $formulario = $result->fetch_assoc();

    if (!$formulario) {
        echo "Formulário não encontrado.";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" x-data="{ open: false }">

<head>
    <meta charset="UTF-8">
    <title>Detalhes do Formulário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Google Fonts (Roboto) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Boxicons (ícones mais modernos) -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        [x-cloak] {
            display: none;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-hover {
            transition: transform 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
        }

        .title-animation {
            animation: slideDown 0.6s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(5px);
        }

        .backdrop-blur {
            backdrop-filter: blur(5px);
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-animation {
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>

    <script>
        // Função para abrir o modal de edição
        function openEditModal() {
            document.getElementById("editModal").classList.remove("hidden");
        }

        // Função para fechar o modal de edição
        function closeEditModal() {
            document.getElementById("editModal").classList.add("hidden");
        }

        // Função para abrir o modal de exclusão
        function openDeleteModal() {
            document.getElementById("deleteModal").classList.remove("hidden");
        }

        // Função para fechar o modal de exclusão
        function closeDeleteModal() {
            document.getElementById("deleteModal").classList.add("hidden");
        }

        // Função para mostrar o alerta de sucesso
        function showSuccessAlert(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className =
                'fixed top-4 right-4 z-50 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-lg max-w-lg';
            alertDiv.innerHTML = `
            <div class="flex items-center">
                <i class='bx bx-check-circle text-2xl mr-2'></i>
                <div>
                    <h4 class="font-bold">Sucesso</h4>
                    <p class="text-sm mt-1">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                    <i class='bx bx-x text-xl'></i>
                </button>
            </div>
        `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 5000);
        }

        // Função para fechar o alerta
        function closeAlert() {
            document.getElementById('successAlert').classList.add('hidden');
        }

        // Função para mostrar o alerta de exclusão
        function showDeleteSuccessAlert(message) {
            const alert = document.getElementById('deleteSuccessAlert');
            document.getElementById('deleteAlertMessage').textContent = message;
            alert.classList.remove('hidden');
            // Esconde o alerta após 3 segundos
            setTimeout(() => {
                closeDeleteAlert();
            }, 3000);
        }

        // Função para fechar o alerta de exclusão
        function closeDeleteAlert() {
            document.getElementById('deleteSuccessAlert').classList.add('hidden');
        }

        // AJAX para editar o formulário
        function editarFormulario() {
            var campo = document.getElementById('campo').value;
            var novoValor = document.getElementById('novoValor').value;
            var id = <?php echo $id; ?>;
            var tipo = '<?php echo $tipo; ?>';

            fetch('editar_formulario_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id,
                        tipo,
                        campo,
                        novoValor
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeEditModal();
                        showSuccessAlert('Formulário atualizado com sucesso!');
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        alert('Erro ao atualizar o formulário.');
                    }
                });
        }

        // AJAX para excluir o formulário
        function excluirFormulario() {
            var id = <?php echo $id; ?>;
            var tipo = '<?php echo $tipo; ?>';

            fetch('excluir_formulario_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id,
                        tipo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeDeleteModal();
                        showDeleteSuccessAlert('Formulário excluído com sucesso!');
                        setTimeout(() => {
                            window.location.href = 'formularios.php';
                        }, 2000);
                    } else {
                        showDeleteSuccessAlert('Erro ao excluir o formulário.');
                    }
                });
        }

        // Função para abrir o modal de confirmação
        function openConfirmModal() {
            document.getElementById("confirm-modal").classList.remove("hidden");
            document.getElementById("confirm-modal").classList.add("flex");
        }

        // Função para fechar o modal de confirmação
        function closeConfirmModal() {
            document.getElementById("confirm-modal").classList.remove("flex");
            document.getElementById("confirm-modal").classList.add("hidden");
        }

        // Modifica a função concluirFormulario para ser chamada após confirmação
        function concluirFormulario() {
            let email = document.getElementById('emailDestino').textContent.trim();
            let nome =
                '<?php echo $tipo == "DAT" ? addslashes($dat1['nome'] ?? '') : addslashes($formulario['nome'] ?? ''); ?>';

            // Validação mais robusta dos dados
            if (!nome) {
                showErrorAlert('Erro: Nome não encontrado no formulário');
                return;
            }

            if (email === 'Email não informado' || !email) {
                showErrorAlert('Não é possível concluir: email não informado no cadastro');
                return;
            }

            if (!validateEmail(email)) {
                showErrorAlert('O email cadastrado é inválido');
                return;
            }

            const loadingButton = document.getElementById('btnConcluir');
            const cancelButton = document.getElementById('btnCancelar');

            loadingButton.disabled = true;
            cancelButton.disabled = true;
            loadingButton.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Enviando email...
    `;

            // Primeiro atualizar o status
            fetch('atualizar_situacao_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: <?php echo $id; ?>,
                        tipo: '<?php echo $tipo; ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success && !data.alreadyCompleted) {
                        throw new Error(data.message || 'Erro desconhecido ao atualizar status');
                    }

                    // Log para debug
                    console.log('Status atualizado, enviando email para:', {
                        email,
                        nome,
                        id: <?php echo $id; ?>
                    });

                    // Enviar email mesmo se já estiver concluído
                    return fetch('processar_email.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: email,
                            nome: nome,
                            id: <?php echo $id; ?>,
                            tipo: '<?php echo $tipo; ?>'
                        })
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Erro ao enviar email');
                    }
                    closeConfirmModal();
                    showSuccessAlert('Protocolo concluído e email enviado com sucesso!');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                })
                .catch(error => {
                    console.error('Erro completo:', error);
                    showErrorAlert(`Erro ao processar: ${error.message}`);
                    // Reativar botões em caso de erro
                    const loadingButton = document.getElementById('btnConcluir');
                    const cancelButton = document.getElementById('btnCancelar');
                    loadingButton.disabled = false;
                    cancelButton.disabled = false;
                    loadingButton.innerHTML = 'Sim, concluir';
                });
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function showErrorAlert(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className =
                'fixed top-4 right-4 z-50 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-lg max-w-lg';
            alertDiv.innerHTML = `
        <div class="flex items-center">
            <i class='bx bx-error-circle text-2xl mr-2'></i>
            <div>
                <h4 class="font-bold">Erro ao processar solicitação</h4>
                <p class="text-sm mt-1">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                <i class='bx bx-x text-xl'></i>
            </button>
        </div>
    `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 8000); // Aumentar tempo para 8 segundos
        }
    </script>
</head>

<body class="bg-gray-100 font-roboto min-h-screen flex flex-col">
    <!-- Success Alert -->
    <div id="successAlert" class="hidden fixed top-4 right-4 z-50">
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-lg flex items-center">
            <i class='bx bx-check text-2xl mr-2'></i>
            <span id="alertMessage">Formulário atualizado com sucesso!</span>
            <button onclick="closeAlert()" class="ml-4 text-green-700 hover:text-green-900">
                <i class='bx bx-x text-xl'></i>
            </button>
        </div>
    </div>

    <!-- Delete Success Alert -->
    <div id="deleteSuccessAlert" class="hidden fixed top-4 right-4 z-50">
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-lg flex items-center">
            <i class='bx bx-trash text-2xl mr-2'></i>
            <span id="deleteAlertMessage">Formulário excluído com sucesso!</span>
            <button onclick="closeDeleteAlert()" class="ml-4 text-red-700 hover:text-red-900">
                <i class='bx bx-x text-xl'></i>
            </button>
        </div>
    </div>

    <!-- Loader -->
    <div x-ref="loading" class="fixed inset-0 bg-white z-50 flex items-center justify-center hidden">
        <span class="material-icons animate-spin text-4xl text-blue-600">autorenew</span>
    </div>

    <!-- Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 flex flex-col h-full">
                <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                <?php echo getSidebarHtml('formularios'); ?>
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
            <?php
            $topbarHtml = getTopbarHtml('Detalhes do Formulário', $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6 bg-gradient-to-br from-gray-50 to-gray-100">
                <!-- Cabeçalho do formulário -->
                <div class="mb-8 title-animation">
                    <div class="gradient-bg rounded-2xl p-6 text-white">
                        <h2 class="text-4xl font-bold mb-2">
                            <?php
                            $tipoFormatado = [
                                'DAT' => 'Declaração de Acidente de Trânsito',
                                'PCD' => 'Solicitação de Cartão PCD',
                                'SAC' => 'Atendimento ao Cidadão',
                                'JARI' => 'Recurso JARI',
                                'Parecer' => 'Parecer Técnico'
                            ][$tipo] ?? $tipo;
                            echo $tipoFormatado;
                            ?>
                        </h2>
                        <p class="text-white/80 flex items-center">
                            <i class='bx bx-file mr-2'></i>
                            Protocolo: #<?php echo $id; ?>
                        </p>
                    </div>
                </div>

                <?php if ($tipo == 'DAT'): ?>
                    <!-- Seção DAT -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Informações Gerais -->
                        <?php if ($dat1): ?>
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="border-b border-gray-200 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Informações Gerais</h3>
                                </div>
                                <div class="p-6">
                                    <?php exibir_dados_formatados($dat1); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Detalhes do Acidente -->
                        <?php if ($dat2): ?>
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="border-b border-gray-200 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Detalhes do Acidente</h3>
                                </div>
                                <div class="p-6">
                                    <?php exibir_dados_formatados($dat2); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Veículos Envolvidos -->
                    <?php if ($dat3): ?>
                        <div class="mt-8">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                    <i class='bx bx-car text-green-600 text-2xl'></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800">Veículos Envolvidos</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <?php foreach ($dat3 as $index => $veiculo): ?>
                                    <div class="glass-effect rounded-2xl shadow-lg p-6 card-hover">
                                        <div class="flex items-center justify-between mb-6">
                                            <h4 class="text-lg font-bold text-gray-700 flex items-center">
                                                <i class='bx bxs-car-crash text-gray-500 mr-2 text-xl'></i>
                                                Veículo <?php echo $index + 1; ?>
                                            </h4>
                                            <span
                                                class="px-3 py-1 rounded-full text-sm <?php echo $index % 2 ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                                #<?php echo $index + 1; ?>
                                            </span>
                                        </div>
                                        <div class="space-y-3">
                                            <?php exibir_dados_formatados($veiculo); ?>

                                            <?php if (!empty($veiculo['damaged_parts'])): ?>
                                                <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                                                    <h5 class="font-semibold text-gray-700 mb-2">Áreas Danificadas:</h5>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <?php
                                                        $damaged_parts = json_decode($veiculo['damaged_parts'], true);
                                                        foreach ($damaged_parts as $part):
                                                            if ($part['checked']):
                                                        ?>
                                                                <div class="flex items-center">
                                                                    <span class="material-icons text-red-500 text-sm mr-1">warning</span>
                                                                    <span
                                                                        class="text-sm"><?php echo ucfirst(str_replace('_', ' ', $part['name'])); ?></span>
                                                                </div>
                                                        <?php
                                                            endif;
                                                        endforeach;
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Outros tipos de formulário -->
                    <div class="glass-effect rounded-2xl shadow-lg p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($formulario as $coluna => $valor): ?>
                                <div class="p-4 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 card-hover">
                                    <label class="text-sm font-medium text-gray-600 block mb-1">
                                        <?php echo ucfirst(str_replace('_', ' ', $coluna)); ?>
                                    </label>
                                    <div class="text-gray-900 font-medium">
                                        <?php echo !empty($valor) ? htmlspecialchars($valor) : '<span class="text-gray-400">Não informado</span>'; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botões de ação -->
                <div class="mt-8 glass-effect rounded-2xl p-4 flex justify-between items-center">
                    <div class="flex space-x-4">
                        <a href="formularios.php"
                            class="flex items-center px-6 py-3 bg-white text-gray-700 rounded-xl hover:bg-gray-50 transition shadow-sm">
                            <i class='bx bx-arrow-back mr-2'></i>
                            Voltar
                        </a>
                        <button onclick="openEditModal()"
                            class="flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition shadow-sm">
                            <i class='bx bx-edit-alt mr-2'></i>
                            Editar
                        </button>
                        <button onclick="openConfirmModal()"
                            class="flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition shadow-sm">
                            <i class='bx bx-check mr-2'></i>
                            Concluir
                        </button>
                    </div>
                    <button onclick="openDeleteModal()"
                        class="flex items-center px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition shadow-sm">
                        <i class='bx bx-trash mr-2'></i>
                        Excluir
                    </button>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">&copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os
                    direitos reservados.</p>
            </footer>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Editar Formulário</h2>
            <label for="campo" class="block mb-2">Selecione o campo a editar:</label>
            <select id="campo" class="w-full mb-4 p-2 border rounded">
                <?php
                $dadosParaEdicao = $tipo == 'DAT' ? array_merge($dat1, $dat2, $dat4) : $formulario;
                foreach ($dadosParaEdicao as $coluna => $valor): ?>
                    <option value="<?php echo $coluna; ?>"><?php echo ucfirst(str_replace('_', ' ', $coluna)); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="novoValor" class="block mb-2">Novo valor:</label>
            <input type="text" id="novoValor" class="w-full mb-4 p-2 border rounded" placeholder="Digite o novo valor">

            <div class="flex justify-end space-x-4">
                <button onclick="closeEditModal()"
                    class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400 transition">Cancelar</button>
                <button onclick="editarFormulario()"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Salvar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Exclusão -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirmar Exclusão</h2>
            <p>Você tem certeza que deseja excluir este formulário?</p>

            <div class="flex justify-end space-x-4 mt-6">
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400 transition">Cancelar</button>
                <button onclick="excluirFormulario()"
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">Excluir</button>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div id="confirm-modal" tabindex="-1"
        class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 justify-center items-center backdrop-blur">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow-lg modal-animation">
                <button type="button" onclick="closeConfirmModal()"
                    class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Fechar modal</span>
                </button>
                <div class="p-6 text-center">
                    <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500">Tem certeza que deseja concluir este protocolo?
                    </h3>

                    <!-- Adicionar informação do email -->
                    <div class="mb-4 text-left p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Uma notificação será enviada para:</p>
                        <p class="text-blue-600 font-medium mt-1" id="emailDestino">
                            <?php
                            $emailDestino = $tipo == 'DAT' ?
                                (isset($dat1['email']) ? $dat1['email'] : 'Email não informado') : (isset($formulario['email']) ? $formulario['email'] : 'Email não informado');
                            echo $emailDestino;
                            ?>
                        </p>
                    </div>

                    <div class="flex justify-center items-center space-x-4">
                        <button id="btnConcluir" onclick="concluirFormulario()" type="button"
                            class="text-white bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                            Sim, concluir
                        </button>
                        <button id="btnCancelar" onclick="closeConfirmModal()" type="button"
                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                            Não, cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>