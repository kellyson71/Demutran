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
        echo "<div><strong>Dados não disponíveis.</strong></div>";
        return;
    }

    foreach ($dados as $coluna => $valor) {
        if (!in_array($coluna, $colunas_ocultas)) {
            $nome_coluna = isset($colunas_personalizadas[$coluna]) ? $colunas_personalizadas[$coluna] : ucfirst(str_replace('_', ' ', $coluna));

            // Verifica se é uma coluna de arquivo ou contém URL
            if (in_array($coluna, $colunas_arquivo) || filter_var($valor, FILTER_VALIDATE_URL)) {
                echo "<div class='flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg'>";
                echo "<strong class='text-gray-700'>" . $nome_coluna . ":</strong>";
                echo "<a href='" . htmlspecialchars($valor) . "' target='_blank' 
                     class='flex items-center gap-2 text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg transition-colors'>
                     <i class='bx bx-file'></i>
                     <span>Visualizar arquivo</span>
                     </a>";
                echo "</div>";
            } else {
                echo "<div class='p-2 hover:bg-gray-50 rounded-lg'>";
                echo "<strong class='text-gray-700'>" . $nome_coluna . ":</strong> ";
                echo "<span class='text-gray-600'>" . (!empty($valor) ? htmlspecialchars($valor) : 'Não informado') . "</span>";
                echo "</div>";
            }
        }
    }
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

    // Nova consulta para obter os veículos do usuário
    $sqlVehicles = "SELECT uv.*, vd.* 
                    FROM user_vehicles uv 
                    LEFT JOIN vehicle_damages vd ON uv.id = vd.user_vehicles_id 
                    WHERE uv.token = ?
                    ORDER BY vd.vehicle_index";
    $stmtVehicles = $conn->prepare($sqlVehicles);
    $stmtVehicles->bind_param('s', $token);
    $stmtVehicles->execute();
    $resultVehicles = $stmtVehicles->get_result();
    $vehicles = [];
    while ($row = $resultVehicles->fetch_assoc()) {
        $vehicles[] = $row;
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

// Marcar como lido após carregar os detalhes
$tabela = match ($tipo) {
    'DAT' => 'DAT4',
    'Parecer' => 'Parecer',
    'SAC' => 'sac',
    'PCD' => 'solicitacao_cartao',
    'JARI' => 'solicitacoes_demutran',
    default => null
};

if ($tabela) {
    $sql = "UPDATE {$tabela} SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
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

        .title-header {
            background-color: #2563eb;
            /* bg-blue-600 sólido */
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
            background: white;
            border: 1px solid #e5e7eb;
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

        // Modificar a função enviarParaFormulario()
        function enviarParaFormulario() {
            const tipo = '<?php echo $tipo; ?>';
            const id = <?php echo $id; ?>;
            const subtipo = '<?php echo $formulario['tipo_solicitacao'] ?? ''; ?>';
            let url;

            // Mapear os tipos de defesa corretamente
            if (tipo === 'JARI') {
                if (subtipo === 'apresentacao_condutor') {
                    // Se for apresentação de condutor, usar gerar_formulario_AP.php
                    url = '../utils/form/gerar_formulario_AP.php?id=' + id;
                } else {
                    // Para outros tipos (jari e defesa_previa), usar gerar_formulario.php
                    const data = {
                        id: id,
                        tipo_solicitacao: subtipo
                    };
                    url = '../utils/form/gerar_formulario.php?' + new URLSearchParams(data).toString();
                }

                if (url) {
                    window.open(url, '_blank');
                } else {
                    showErrorAlert('Tipo de formulário não suportado');
                }
            }
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
                    <div class="title-header rounded-lg p-6 text-white">
                        <h2 class="text-4xl font-bold mb-2">
                            <?php
                            if ($tipo == 'JARI') {
                                // Para formulários da tabela solicitacoes_demutran
                                $subtipo = $formulario['tipo_solicitacao'] ?? '';
                                switch ($subtipo) {
                                    case 'defesa_previa':
                                        echo 'Defesa Prévia';
                                        break;
                                    case 'jari':
                                        echo 'Recurso JARI';
                                        break;
                                    case 'apresentacao_condutor':
                                        echo 'Apresentação de Condutor';
                                        break;
                                    default:
                                        echo 'Formulário de Defesa';
                                }
                            } elseif ($tipo == 'PCD') {
                                // Para formulários da tabela solicitacao_cartao
                                $subtipo = $formulario['tipo_solicitacao'] ?? '';
                                switch ($subtipo) {
                                    case 'pcd':
                                        echo 'Cartão PCD';
                                        break;
                                    case 'idoso':
                                        echo 'Cartão Idoso';
                                        break;
                                    default:
                                        echo 'Solicitação de Cartão';
                                }
                            } else {
                                // Para outros tipos de formulário
                                $tipoFormatado = [
                                    'DAT' => 'Declaração de Acidente de Trânsito',
                                    'SAC' => 'Atendimento ao Cidadão',
                                    'Parecer' => 'Parecer Técnico'
                                ][$tipo] ?? $tipo;
                                echo $tipoFormatado;
                            }
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
                    <div class="space-y-8">
                        <!-- Informações Gerais -->
                        <?php if ($dat1): ?>
                            <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Informações do Solicitante</h3>
                                </div>
                                <div class="divide-y divide-gray-200">
                                    <?php
                                    foreach ($dat1 as $campo => $valor) {
                                        if (!in_array($campo, ['token', 'id'])) {
                                            $labelCampo = ucfirst(str_replace('_', ' ', $campo));
                                            echo '<div class="px-6 py-4 grid grid-cols-3">';
                                            echo '<div class="text-sm font-medium text-gray-600">' . $labelCampo . '</div>';
                                            echo '<div class="col-span-2 text-sm text-gray-900">' .
                                                (!empty($valor) ? htmlspecialchars($valor) :
                                                    '<span class="text-gray-400">Não informado</span>') . '</div>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Detalhes do Acidente -->
                        <?php if ($dat2): ?>
                            <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Detalhes do Acidente</h3>
                                </div>
                                <div class="divide-y divide-gray-200">
                                    <?php
                                    foreach ($dat2 as $campo => $valor) {
                                        if (!in_array($campo, ['token', 'id'])) {
                                            $labelCampo = ucfirst(str_replace('_', ' ', $campo));
                                            echo '<div class="px-6 py-4 grid grid-cols-3">';
                                            echo '<div class="text-sm font-medium text-gray-600">' . $labelCampo . '</div>';
                                            echo '<div class="col-span-2 text-sm text-gray-900">' .
                                                (!empty($valor) ? htmlspecialchars($valor) :
                                                    '<span class="text-gray-400">Não informado</span>') . '</div>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Veículos Envolvidos -->
                        <?php if (!empty($vehicles)): ?>
                            <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Veículos Envolvidos</h3>
                                </div>
                                <?php foreach ($vehicles as $index => $veiculo): ?>
                                    <div class="border-b border-gray-200 last:border-b-0">
                                        <div class="px-6 py-4">
                                            <h4 class="text-base font-medium text-gray-900 mb-4">Veículo
                                                <?php echo $veiculo['vehicle_index']; ?></h4>
                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                                <?php
                                                // Informações gerais do veículo
                                                $camposVeiculo = [
                                                    'total_vehicles' => 'Total de veículos',
                                                    // Adicione outros campos relevantes aqui
                                                ];

                                                foreach ($camposVeiculo as $campo => $label) {
                                                    if (isset($veiculo[$campo])) {
                                                        echo '<div class="grid grid-cols-2">';
                                                        echo '<div class="text-sm font-medium text-gray-600">' . $label . ':</div>';
                                                        echo '<div class="text-sm text-gray-900">' .
                                                            (!empty($veiculo[$campo]) ? htmlspecialchars($veiculo[$campo]) :
                                                                '<span class="text-gray-400">Não informado</span>') . '</div>';
                                                        echo '</div>';
                                                    }
                                                }
                                                ?>
                                            </div>

                                            <!-- Seção de áreas danificadas -->
                                            <div class="mt-6 border-t border-gray-100 pt-4">
                                                <h5 class="text-sm font-medium text-gray-900 mb-3">Áreas Danificadas</h5>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <?php
                                                    $areasDanificadas = [
                                                        'dianteira_direita' => 'Dianteira Direita',
                                                        'dianteira_esquerda' => 'Dianteira Esquerda',
                                                        'lateral_direita' => 'Lateral Direita',
                                                        'lateral_esquerda' => 'Lateral Esquerda',
                                                        'traseira_direita' => 'Traseira Direita',
                                                        'traseira_esquerda' => 'Traseira Esquerda'
                                                    ];

                                                    foreach ($areasDanificadas as $campo => $label) {
                                                        if (isset($veiculo[$campo]) && $veiculo[$campo]) {
                                                            echo '<div class="flex items-center space-x-2">';
                                                            echo '<div class="w-2 h-2 rounded-full bg-red-500"></div>';
                                                            echo '<span class="text-sm text-gray-600">' . $label . '</span>';
                                                            echo '</div>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>

                                            <!-- Informações de carga -->
                                            <?php if ($veiculo['has_load_damage']): ?>
                                                <div class="mt-6 border-t border-gray-100 pt-4">
                                                    <h5 class="text-sm font-medium text-gray-900 mb-3">Informações da Carga</h5>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div class="text-sm">
                                                            <span class="font-medium text-gray-600">Nota Fiscal:</span>
                                                            <span
                                                                class="text-gray-900"><?php echo htmlspecialchars($veiculo['nota_fiscal'] ?? 'Não informado'); ?></span>
                                                        </div>
                                                        <div class="text-sm">
                                                            <span class="font-medium text-gray-600">Tipo de Mercadoria:</span>
                                                            <span
                                                                class="text-gray-900"><?php echo htmlspecialchars($veiculo['tipo_mercadoria'] ?? 'Não informado'); ?></span>
                                                        </div>
                                                        <div class="text-sm">
                                                            <span class="font-medium text-gray-600">Valor Total:</span>
                                                            <span
                                                                class="text-gray-900"><?php echo 'R$ ' . number_format($veiculo['valor_total'] ?? 0, 2, ',', '.'); ?></span>
                                                        </div>
                                                        <div class="text-sm">
                                                            <span class="font-medium text-gray-600">Estimativa de Danos:</span>
                                                            <span
                                                                class="text-gray-900"><?php echo 'R$ ' . number_format($veiculo['estimativa_danos'] ?? 0, 2, ',', '.'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Outros tipos de formulário -->
                    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Detalhes da Solicitação</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <?php
                            foreach ($formulario as $campo => $valor) {
                                if (!in_array($campo, ['token', 'id'])) {  // Correção aqui
                                    $labelCampo = ucfirst(str_replace('_', ' ', $campo));
                                    echo '<div class="px-6 py-4 grid grid-cols-3">';
                                    echo '<div class="text-sm font-medium text-gray-600">' . $labelCampo . '</div>';
                                    echo '<div class="col-span-2 text-sm text-gray-900">' .
                                        (!empty($valor) ? htmlspecialchars($valor) :
                                            '<span class="text-gray-400">Não informado</span>') . '</div>';
                                    echo '</div>';
                                }
                            }
                            ?>
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

                        <!-- Botão Ver Formulário/Documento -->
                        <?php
                        $formUrl = '';
                        $btnIcon = 'bx-file';
                        $btnText = 'Ver Formulário';

                        switch ($tipo) {
                            case 'JARI':
                                // Se for JARI, verificar o subtipo
                                $subtipo = $formulario['tipo_solicitacao'] ?? '';
                                $formUrl = "javascript:void(0);";
                                $btnIcon = 'bx-file-blank';

                                switch ($subtipo) {
                                    case 'jari':
                                        $btnText = 'Gerar JARI';
                                        break;
                                    case 'defesa_previa':
                                        $btnText = 'Gerar Defesa Prévia';
                                        break;
                                    case 'apresentacao_condutor':
                                        $btnText = 'Gerar Apresentação de Condutor';
                                        break;
                                    default:
                                        $btnText = 'Gerar Defesa';
                                }
                                break;
                            case 'apresentacao_condutor':
                                $formUrl = '../utils/form/gerar_formulario_AP.php';
                                $btnIcon = 'bx-car';
                                $btnText = 'Ver Apresentação';
                                break;
                            case 'PCD':
                                $formUrl = '../utils/form/gerar_formulario_cartao.php';
                                $btnIcon = 'bx-id-card';
                                $btnText = 'Ver Cartão';
                                break;
                            case 'DAT':
                                $formUrl = '../utils/form/gerar_formulario_DAT.php';
                                $btnIcon = 'bx-file';
                                $btnText = 'Ver DAT';
                                break;
                            case 'Parecer':
                                $formUrl = '../utils/form/gerar_formulario_parecer.php';
                                $btnIcon = 'bx-clipboard';
                                $btnText = 'Ver Parecer';
                                break;
                            case 'SAC':
                                $formUrl = '../utils/form/gerar_formulario.php';
                                $btnIcon = 'bx-message-square-detail';
                                $btnText = 'Ver Atendimento';
                                break;
                        }

                        if ($formUrl): ?>
                            <!-- Botão Ver Documento -->
                            <?php if ($tipo == 'JARI'): ?>
                                <a href="#" onclick="enviarParaFormulario()"
                                    class="flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                    <i class='bx <?php echo $btnIcon; ?> text-xl mr-2'></i>
                                    <span class="font-medium"><?php echo $btnText; ?></span>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo $formUrl . '?id=' . $id . '&tipo=' . strtolower($tipo); ?>" target="_blank"
                                    class="flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                    <i class='bx <?php echo $btnIcon; ?> text-xl mr-2'></i>
                                    <span class="font-medium"><?php echo $btnText; ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <button onclick="openEditModal()"
                            class="flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class='bx bx-edit-alt mr-2'></i>
                            Editar
                        </button>
                        <button onclick="openConfirmModal()"
                            class="flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                            <i class='bx bx-check mr-2'></i>
                            Concluir
                        </button>
                    </div>
                    <button onclick="openDeleteModal()"
                        class="flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
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
                            </div>
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


</div>
</div>
</body>

</html>