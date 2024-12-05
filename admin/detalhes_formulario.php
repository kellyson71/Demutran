<?php
session_start();
include '../env/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obter o ID e o tipo do formulário via GET
$id = $_GET['id'];
$tipo = $_GET['tipo'];

function obterUltimosFormulariosSAC($conn) {
    $sql = "SELECT * FROM sac ORDER BY id DESC LIMIT 2";
    return $conn->query($sql);
}

function contarNotificacoesNaoLidas($conn) {
    $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE lida = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

$sacFormularios = obterUltimosFormulariosSAC($conn);
$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

// Atualizar a função exibir_dados_formatados para tratar URLs
function exibir_dados_formatados($dados) {
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
        const alert = document.getElementById('successAlert');
        document.getElementById('alertMessage').textContent = message;
        alert.classList.remove('hidden');
        // Esconde o alerta após 3 segundos
        setTimeout(() => {
            closeAlert();
        }, 3000);
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
                <nav class="space-y-2 flex-1">
                    <a href="dashboard.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">dashboard</span>
                        <span class="ml-3">Dashboard</span>
                    </a>
                    <a href="formularios.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                        <span class="material-icons">assignment</span>
                        <span class="ml-3 font-semibold">Formulários</span>
                    </a>
                    <a href="gerenciar_noticias.php"
                        class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                        <span class="material-icons">article</span>
                        <span class="ml-3">Notícias</span>
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
                    <h2 class="text-xl font-semibold text-gray-800">Detalhes do Formulário</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative focus:outline-none">
                            <span class="material-icons text-gray-700">notifications</span>
                            <?php if ($notificacoesNaoLidas > 0): ?>
                            <span class="absolute top-0 right-0 bg-red-600 text-white rounded-full px-1 text-xs">
                                <?php echo $notificacoesNaoLidas; ?>
                            </span>
                            <?php endif; ?>
                        </button>
                        <!-- Notification dropdown content -->
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50">
                            <!-- ... notification content ... -->
                        </div>
                    </div>

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
                        <!-- ...existing code... -->
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
                    <div class="glass-effect rounded-2xl shadow-lg p-6 card-hover">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                <i class='bx bx-info-circle text-blue-600 text-2xl'></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Informações Gerais</h3>
                        </div>
                        <div class="space-y-4 divide-y divide-gray-100">
                            <?php exibir_dados_formatados($dat1); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Detalhes do Acidente -->
                    <?php if ($dat2): ?>
                    <div class="glass-effect rounded-2xl shadow-lg p-6 card-hover">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                                <i class='bx bx-error text-red-600 text-2xl'></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Detalhes do Acidente</h3>
                        </div>
                        <div class="space-y-4 divide-y divide-gray-100">
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
                                <?php echo !empty($valor) ? htmlspecialchars($valor) : '<span class="text-gray-400">Não informado</span>';?>
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
</body>

</html>