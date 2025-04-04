<?php
include 'env/config.php';

// Função helper para tratar o caminho das imagens
function get_image_path($path)
{
    if (empty($path)) {
        return 'assets/placeholder.jpg';
    }
    return $path; // Retorna o caminho como está no banco
}

// Definir o número de notícias por página
$noticias_por_pagina = 6;

// Verificar em qual página o usuário está
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_atual < 1) $pagina_atual = 1;

// Calcular o offset para a consulta SQL
$offset = ($pagina_atual - 1) * $noticias_por_pagina;

// Buscar o total de notícias (apenas publicadas)
$sql_total = "SELECT COUNT(*) as total FROM noticias WHERE data_publicacao <= CURDATE()";
$result_total = $conn->query($sql_total);
$total_noticias = $result_total->fetch_assoc()['total'];

// Calcular o número total de páginas
$total_paginas = ceil($total_noticias / $noticias_por_pagina);

// Buscar as notícias para a página atual (apenas publicadas)
$sql_noticias = "SELECT * FROM noticias 
                 WHERE data_publicacao <= CURDATE() 
                 ORDER BY data_publicacao DESC 
                 LIMIT $offset, $noticias_por_pagina";
$result_noticias = $conn->query($sql_noticias);

$noticias = [];

if ($result_noticias->num_rows > 0) {
    while ($row = $result_noticias->fetch_assoc()) {
        $noticias[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <!-- Cabeçalho padrão com meta tags, links de estilo, etc. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todas as Notícias - DEMUTRAN Pau dos Ferros</title>
    <link rel="icon" href="./assets/icon.png" type="image/png" />

    <!-- Meta tags adicionais -->
    <meta name="description" content="Confira todas as notícias e atualizações do DEMUTRAN Pau dos Ferros.">
    <meta name="keywords" content="DEMUTRAN, Pau dos Ferros, Notícias, Atualizações, Trânsito">
    <meta name="author" content="Departamento Municipal de Trânsito - Pau dos Ferros">

    <!-- Open Graph e Twitter Cards (opcional) -->

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
                <a href="../DAT/escolher-dat.html" class="hover:text-green-300">DAT</a>
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
            <a href="../DAT/escolher-dat.html" class="block px-4 py-2 text-white hover:bg-green-500">DAT</a>
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
        <div class="text-left mb-4">
            <a href="index.php" class="inline-block bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700">
                &larr; Voltar para a Página Inicial
            </a>
        </div>

        <h1 class="text-4xl font-bold text-center text-gray-800 mb-6">
            Todas as Notícias
        </h1>
        <p class="text-gray-600 text-center max-w-4xl mx-auto mb-8">
            Confira abaixo todas as notícias e atualizações do DEMUTRAN Pau dos Ferros.
        </p>

        <!-- Grid de Notícias -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($noticias as $noticia): ?>
                <a href="noticia.php?id=<?php echo $noticia['id']; ?>"
                    class="bg-white shadow-lg rounded-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="h-48">
                        <img src="<?php echo get_image_path($noticia['imagem_url']); ?>"
                            alt="<?php echo htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                            class="object-cover w-full h-full">
                    </div>
                    <div class="p-4">
                        <h3 class="text-gray-900 font-bold text-xl mb-2">
                            <?php echo htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                        <p class="text-gray-600 text-sm mb-4">
                            <?php echo htmlspecialchars($noticia['resumo'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <span class="text-green-600">Leia mais</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Paginação -->
        <div class="mt-8 flex justify-center">
            <?php if ($pagina_atual > 1): ?>
                <a href="?pagina=<?php echo $pagina_atual - 1; ?>"
                    class="px-4 py-2 mx-1 bg-green-600 text-white rounded hover:bg-green-700">Anterior</a>
            <?php endif; ?>

            <?php
            // Exibir links para as páginas
            for ($i = 1; $i <= $total_paginas; $i++):
                if ($i == $pagina_atual):
            ?>
                    <span class="px-4 py-2 mx-1 bg-green-600 text-white rounded"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?pagina=<?php echo $i; ?>"
                        class="px-4 py-2 mx-1 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"><?php echo $i; ?></a>
            <?php
                endif;
            endfor;
            ?>

            <?php if ($pagina_atual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_atual + 1; ?>"
                    class="px-4 py-2 mx-1 bg-green-600 text-white rounded hover:bg-green-700">Próxima</a>
            <?php endif; ?>
        </div>

        <!-- Botão Voltar ao Topo -->
        <div class="text-center mt-8">
            <a href="#top" class="inline-flex items-center text-green-600 hover:text-green-800">
                <i class="fas fa-arrow-up mr-2"></i>
                <span>Voltar ao Topo</span>
            </a>
        </div>
    </main>

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