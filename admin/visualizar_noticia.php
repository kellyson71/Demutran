<?php
session_start();
include '../env/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obter ID da notícia
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: gerenciar_noticias.php');
    exit();
}

// Buscar notícia específica
$stmt = $conn->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$noticia = $result->fetch_assoc();

if (!$noticia) {
    header('Location: gerenciar_noticias.php');
    exit();
}

// Função para formatar a data
function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

// Função para contar notificações não lidas
function contarNotificacoesNaoLidas($conn) {
    $sql = "SELECT COUNT(*) AS total FROM notificacoes WHERE lida = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($noticia['titulo']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-100">
    <!-- ... existing header and sidebar code ... -->

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <!-- ... existing topbar code ... -->

        <!-- Main -->
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            <!-- Breadcrumb -->
            <nav class="mb-8 flex items-center space-x-2 text-sm text-gray-500">
                <a href="gerenciar_noticias.php" class="hover:text-blue-600">Notícias</a>
                <span class="material-icons text-xs">chevron_right</span>
                <span class="text-gray-900">Visualizar</span>
            </nav>

            <!-- Notícia Content -->
            <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm overflow-hidden">
                <!-- Imagem da Notícia -->
                <?php if (!empty($noticia['imagem_url'])): ?>
                <div class="relative h-[400px] w-full">
                    <img src="<?php echo htmlspecialchars($noticia['imagem_url']); ?>"
                        alt="<?php echo htmlspecialchars($noticia['titulo']); ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                </div>
                <?php endif; ?>

                <!-- Conteúdo -->
                <div class="p-8">
                    <!-- Meta Info -->
                    <div class="flex items-center gap-4 mb-6 text-sm">
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full font-medium">
                            Publicada
                        </span>
                        <span class="text-gray-500 flex items-center gap-1">
                            <span class="material-icons text-sm">calendar_today</span>
                            <?php echo formatarData($noticia['data_publicacao']); ?>
                        </span>
                    </div>

                    <!-- Título -->
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">
                        <?php echo htmlspecialchars($noticia['titulo']); ?>
                    </h1>

                    <!-- Resumo -->
                    <p class="text-lg text-gray-600 mb-8 font-medium">
                        <?php echo htmlspecialchars($noticia['resumo']); ?>
                    </p>

                    <!-- Conteúdo Principal -->
                    <div class="prose max-w-none">
                        <?php echo nl2br(htmlspecialchars($noticia['conteudo'])); ?>
                    </div>

                    <!-- Ações -->
                    <div class="mt-12 flex items-center justify-between pt-8 border-t">
                        <a href="gerenciar_noticias.php"
                            class="inline-flex items-center text-gray-600 hover:text-gray-900">
                            <span class="material-icons mr-2">arrow_back</span>
                            Voltar para lista
                        </a>

                        <div class="flex items-center gap-4">
                            <a href="editar_noticia.php?id=<?php echo $noticia['id']; ?>"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <span class="material-icons mr-2">edit</span>
                                Editar
                            </a>
                            <button onclick="confirmarExclusao(<?php echo $noticia['id']; ?>)"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                <span class="material-icons mr-2">delete</span>
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white shadow-md py-4 px-6">
            <p class="text-gray-600 text-center">
                &copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os direitos reservados.
            </p>
        </footer>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="modal-confirmacao"
        class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Confirmar Exclusão</h3>
            <p class="text-gray-600 mb-6">Tem certeza que deseja excluir esta notícia? Esta ação não pode ser desfeita.
            </p>
            <div class="flex justify-end gap-4">
                <button onclick="fecharModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>
                <button onclick="excluirNoticia()"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                    Confirmar Exclusão
                </button>
            </div>
        </div>
    </div>

    <script>
    let noticiaId = <?php echo $id; ?>;

    function confirmarExclusao(id) {
        document.getElementById('modal-confirmacao').classList.remove('hidden');
    }

    function fecharModal() {
        document.getElementById('modal-confirmacao').classList.add('hidden');
    }

    function excluirNoticia() {
        fetch('./excluir_formulario_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: noticiaId,
                    tipo: 'noticias'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'gerenciar_noticias.php';
                } else {
                    alert('Erro ao excluir notícia: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erro na requisição: ' + error);
            });
    }
    </script>
</body>

</html>