<?php
// noticia.php
include 'env/config.php';

// Verificar se o parâmetro 'id' foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Buscar a notícia pelo ID
    $stmt = $conn->prepare("SELECT * FROM noticias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar se a notícia existe
    if ($result->num_rows > 0) {
        $news = $result->fetch_assoc();
    } else {
        // Notícia não encontrada
        header("HTTP/1.0 404 Not Found");
        echo "Notícia não encontrada.";
        exit();
    }
    $stmt->close();
} else {
    // ID inválido ou não fornecido
    header("HTTP/1.0 400 Bad Request");
    echo "Requisição inválida.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <!-- Metadados -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['titulo'], ENT_QUOTES, 'UTF-8'); ?> - DEMUTRAN Pau dos Ferros</title>
    <link rel="icon" href="./assets/icon.png" type="image/png" />

    <!-- Descrição e Palavras-chave -->
    <meta name="description" content="<?php echo htmlspecialchars($news['resumo'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="keywords" content="DEMUTRAN, Pau dos Ferros, Trânsito, Notícias">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($news['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($news['resumo'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo $news['imagem_url']; ?>">
    <meta property="og:url" content="https://www.detran.paudosferros.gov.br/noticia.php?id=<?php echo $news['id']; ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($news['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($news['resumo'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image" content="<?php echo $news['imagem_url']; ?>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
</head>

<body class="font-roboto bg-gray-100">
    <!-- Início da Página -->
    <div id="top"></div>

    <!-- Top Bar -->
    <header class="bg-green-600 text-white shadow-md w-full fixed top-0 left-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Logo -->
            <div class="text-lg font-semibold">
                <a href="index.php" class="hover:text-green-300">DEMUTRAN</a>
            </div>

            <!-- Links -->
            <nav class="hidden md:flex space-x-6">
                <a href="../PCD/index.html" class="hover:text-green-300">Cartão Vaga Especial</a>
                <a href="../Defesa/index.html" class="hover:text-green-300">Defesa Prévia/JARI</a>
                <a href="../DAT/index.php" class="hover:text-green-300">DAT</a>
                <a href="../contato/index.html" class="hover:text-green-300">Contato</a>
            </nav>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button id="menu-btn" class="text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-green-600">
            <a href="../PCD/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Cartão Vaga Especial</a>
            <a href="../Defesa/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Defesa Prévia/JARI</a>
            <a href="../DAT/index.php" class="block px-4 py-2 text-white hover:bg-green-500">DAT</a>
            <a href="../contato/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Contato</a>
        </div>
    </header>

    <!-- Topbar Secundária -->
    <div class="bg-gray-200 border-b border-gray-300 mt-16">
        <div class="container mx-auto py-2 text-center">
            <a href="https://paudosferros.rn.gov.br/secretaria.php?sec=19"
                class="text-gray-800 hover:text-gray-600">Demutran – DEPARTAMENTO MUNICIPAL DE TRÂNSITO</a>
            <span class="text-gray-600 mx-2">|</span>
            <a href="https://paudosferros.rn.gov.br/lai.php" class="text-gray-800 hover:text-gray-600">Acesso à
                Informação</a>
            <span class="text-gray-600 mx-2">-</span>
            <a href="https://paudosferros.rn.gov.br/omunicipio.php" class="text-gray-800 hover:text-gray-600">Sobre a
                Prefeitura</a>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <main class="container mx-auto py-8">
        <!-- Título da Notícia -->
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-6">
            <?php echo htmlspecialchars($news['titulo'], ENT_QUOTES, 'UTF-8'); ?>
        </h1>

        <!-- Data de Publicação -->
        <p class="text-gray-600 text-center mb-4">
            Publicado em: <?php echo date('d/m/Y', strtotime($news['data_publicacao'])); ?>
        </p>

        <!-- Imagem Principal -->
        <div class="flex justify-center mb-8">
            <img src="<?php echo $news['imagem_url']; ?>"
                alt="<?php echo htmlspecialchars($news['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                class="w-full md:w-2/3 rounded-lg shadow-lg">
        </div>

        <!-- Conteúdo da Notícia -->
        <div class="text-gray-800 leading-relaxed max-w-3xl mx-auto">
            <?php echo nl2br(htmlspecialchars($news['conteudo'], ENT_QUOTES, 'UTF-8')); ?>
        </div>

        <!-- Botão Voltar -->
        <div class="text-center mt-8">
            <a href="index.php" class="inline-flex items-center text-green-600 hover:text-green-800">
                <i class="fas fa-arrow-left mr-2"></i>
                <span>Voltar para a Página Inicial</span>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-8">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
            <div>
                <h2 class="text-lg font-semibold mb-2">INSTITUCIONAL</h2>
                <p>PREFEITO(A): MARIANNA ALMEIDA</p>
                <p>CNPJ: 08.148.421/0001-76</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-2">CONTATOS</h2>
                <p>(84) 99944-0704</p>
                <p>segovpmpf@gmail.com</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-2">ENDEREÇO E HORÁRIO</h2>
                <p>
                    Av. Getúlio Vargas, 1371 - Centro, Pau dos Ferros - RN, 59900-000
                </p>
                <p>SEGUNDA A SEXTA-FEIRA, DAS 7H ÀS 13H</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-2">REDES SOCIAIS</h2>
                <div class="flex justify-center space-x-4 mt-2">
                    <a href="https://www.instagram.com/prefeituradepaudosferros/"><i
                            class="fab fa-instagram text-2xl"></i></a>
                    <a href="https://www.facebook.com/prefeituradepaudosferros/"><i
                            class="fab fa-facebook text-2xl"></i></a>
                    <a href="https://twitter.com/paudosferros"><i class="fab fa-twitter text-2xl"></i></a>
                    <a href="https://www.youtube.com/c/prefeituramunicipaldepaudosferros"><i
                            class="fab fa-youtube text-2xl"></i></a>
                    <a href="https://wa.me/558499440704"><i class="fab fa-whatsapp text-2xl"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- JavaScript -->
    <script>
    // Mobile Menu Toggle
    const menuBtn = document.getElementById("menu-btn");
    const mobileMenu = document.getElementById("mobile-menu");

    menuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
    });
    </script>
</body>

</html>