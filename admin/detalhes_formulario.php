<?php
session_start();
include 'config.php';

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

function exibir_dados_formatados($dados) {
    // Definir os rótulos personalizados dentro da função
    $colunas_personalizadas = [
        'created_at' => 'Criado em',
        'damage_system' => 'Sistema de danos',
        'damaged_parts' => 'Partes danificadas',
        // ... (adicione todos os rótulos personalizados aqui)
    ];
    // Definir as colunas que devem ser ocultadas
    $colunas_ocultas = ['token', 'id', 'damaged_parts'];

    // Se $dados estiver vazio, exibir "Dados não disponíveis."
    if (empty($dados)) {
        echo "<div><strong>Dados não disponíveis.</strong></div>";
        return;
    }

    foreach ($dados as $coluna => $valor) {
        // Verificar se a coluna deve ser ocultada
        if (!in_array($coluna, $colunas_ocultas)) {
            // Substituir nome da coluna por um personalizado, se existir
            $nome_coluna = isset($colunas_personalizadas[$coluna]) ? $colunas_personalizadas[$coluna] : ucfirst(str_replace('_', ' ', $coluna));

            // Exibir a coluna e o valor, ou 'Não informado' se estiver vazio
            echo "<div><strong>" . $nome_coluna . ":</strong> " . (!empty($valor) ? htmlspecialchars($valor) : 'Não informado') . "</div>";
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

    <style>
        [x-cloak] { display: none; }
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

        // AJAX para editar o formulário
        function editarFormulario() {
            var campo = document.getElementById('campo').value;
            var novoValor = document.getElementById('novoValor').value;
            var id = <?php echo $id; ?>;
            var tipo = '<?php echo $tipo; ?>';

            fetch('editar_formulario_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, tipo, campo, novoValor })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Formulário atualizado com sucesso!');
                    location.reload(); // Recarrega a página para ver as mudanças
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
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, tipo })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Formulário excluído com sucesso!');
                    window.location.href = 'formularios.php'; // Redireciona para a lista de formulários
                } else {
                    alert('Erro ao excluir o formulário.');
                }
            });
        }
    </script>
</head>
<body class="bg-gray-100 font-roboto min-h-screen flex flex-col">
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
                    <a href="gerenciar_noticias.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
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

        <!-- Mobile Sidebar -->
        <div x-show="open" @click.away="open = false" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden">
            <aside class="w-64 bg-white h-full shadow-md">
                <div class="p-6">
                    <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                    <nav class="space-y-2">
                        <a href="dashboard.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">dashboard</span>
                            <span class="ml-3">Dashboard</span>
                        </a>
                        <a href="formularios.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                            <span class="material-icons">assignment</span>
                            <span class="ml-3 font-semibold">Formulários</span>
                        </a>
                        <a href="gerenciar_noticias.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">article</span>
                            <span class="ml-3">Notícias</span>
                        </a>
                        <a href="usuarios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">people</span>
                            <span class="ml-3">Usuários</span>
                        </a>
                        <a href="perfil.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">person</span>
                            <span class="ml-3">Perfil</span>
                        </a>
                        <a href="logout.php" class="flex items-center p-2 text-red-600 hover:bg-red-50 rounded">
                            <span class="material-icons">logout</span>
                            <span class="ml-3">Sair</span>
                        </a>
                    </nav>
                </div>
            </aside>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <!-- Mobile Menu Button -->
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
                                <span class="absolute top-0 right-0 bg-red-600 text-white rounded-full px-1 text-xs"><?php echo $notificacoesNaoLidas; ?></span>
                            <?php endif; ?>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50">
                            <div class="p-4 border-b text-gray-700 font-bold">Últimos Formulários SAC</div>
                            <ul>
                                <?php while($form = $sacFormularios->fetch_assoc()): ?>
                                    <li class="p-4 border-b hover:bg-gray-50">
                                        <a href="detalhes_formulario.php?id=<?php echo $form['id']; ?>&tipo=SAC" class="block">
                                            <p class="font-medium text-gray-800"><?php echo $form['nome']; ?></p>
                                            <p class="text-sm text-gray-600"><?php echo $form['assunto']; ?></p>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- User Profile -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center focus:outline-none">
                            <img src="avatar.png" alt="Avatar" class="w-8 h-8 rounded-full">
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-50">
                            <div class="p-4 border-b text-gray-700 font-bold"><?php echo $_SESSION['usuario_nome']; ?></div>
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
            <main class="flex-1 overflow-y-auto p-6">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <?php if ($tipo == 'DAT'): ?>
                        <h2 class="text-2xl font-bold text-gray-700 mb-6">Declaração de Acidente (DAT)</h2>

                        <!-- Exibir dados de DAT1 -->
                        <?php if ($dat1): ?>
                            <h3 class="text-lg font-bold mb-4">Informações Gerais</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <?php exibir_dados_formatados($dat1); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($dat2): ?>
                            <h3 class="text-lg font-bold mb-4">Detalhes do Acidente</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <?php exibir_dados_formatados($dat2); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($dat3): ?>
                            <h3 class="text-lg font-bold mb-4">Veículos Envolvidos</h3>
                            <?php foreach ($dat3 as $index => $response): ?>
                                <div class="border p-4 mb-4 rounded-lg shadow-md">
                                    <h4 class="font-bold mb-2">Veículo #<?php echo $index + 1; ?></h4>
                                    <?php exibir_dados_formatados($response); ?>

                                    <!-- Exibir damaged_parts de forma organizada -->
                                    <?php if (!empty($response['damaged_parts'])): ?>
                                        <?php $damaged_parts = json_decode($response['damaged_parts'], true); // Decodifica o JSON ?>
                                        <div class="mt-4">
                                            <strong>Partes Danificadas:</strong>
                                            <ul class="list-disc ml-5">
                                                <?php foreach ($damaged_parts as $part): ?>
                                                    <?php if ($part['checked']): ?>
                                                        <li><?php echo ucfirst(str_replace('_', ' ', $part['name'])); ?></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ($dat4): ?>
                            <h3 class="text-lg font-bold mb-4">Observações Adicionais</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <?php exibir_dados_formatados($dat4); ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <h2 class="text-2xl font-bold text-gray-700 mb-6">Detalhes do Formulário</h2>

                        <!-- Exibir todos os dados do formulário para PCD, SAC, ou JARI -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <?php foreach ($formulario as $coluna => $valor): ?>
                                <?php if (strpos($coluna, 'url') !== false && !empty($valor)): ?>
                                    <!-- Exibir os links dos documentos -->
                                    <div><strong><?php echo ucfirst(str_replace('_', ' ', str_replace('_url', '', $coluna))); ?>:</strong>
                                        <a href="<?php echo htmlspecialchars($valor); ?>" target="_blank" class="text-blue-500 hover:underline">Ver Documento</a>
                                    </div>
                                <?php else: ?>
                                    <!-- Exibir os dados normais -->
                                    <div><strong><?php echo ucfirst(str_replace('_', ' ', $coluna)); ?>:</strong> <?php echo !empty($valor) ? htmlspecialchars($valor) : 'Não informado';?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Seção de Documentos Enviados -->
                        <h3 class="font-bold text-lg mt-8 mb-4">Documentos Enviados:</h3>
                        <ul class="list-disc ml-5">
                            <?php foreach ($formulario as $coluna => $valor): ?>
                                <?php if (strpos($coluna, 'url') !== false && !empty($valor)): ?>
                                    <?php
                                    // Formatar o nome da coluna para algo mais legível
                                    $colunaFormatada = ucfirst(str_replace('_', ' ', str_replace('_url', '', $coluna)));
                                    ?>
                                    <li><a href="<?php echo htmlspecialchars($valor); ?>" target="_blank" class="text-blue-500 hover:underline"><?php echo $colunaFormatada; ?></a></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <!-- Botões de ação -->
                    <div class="mt-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                        <div class="flex space-x-4">
                            <a href="formularios.php" class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400 transition">Voltar</a>
                            <button onclick="openEditModal()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Editar</button>
                        </div>
                        <div class="flex space-x-4">
                            <button onclick="openDeleteModal()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">Excluir</button>
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
                <button onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400 transition">Cancelar</button>
                <button onclick="editarFormulario()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Salvar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Exclusão -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirmar Exclusão</h2>
            <p>Você tem certeza que deseja excluir este formulário?</p>

            <div class="flex justify-end space-x-4 mt-6">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400 transition">Cancelar</button>
                <button onclick="excluirFormulario()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">Excluir</button>
            </div>
        </div>
    </div>
</body>
</html>
