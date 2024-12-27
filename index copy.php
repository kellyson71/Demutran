<?php
// index.php
include 'env/config.php';

// Buscar todas as notícias para o carrossel
$sql_carousel = "SELECT * FROM noticias ORDER BY data_publicacao DESC";
$result_carousel = $conn->query($sql_carousel);

$carouselItems = [];

if ($result_carousel->num_rows > 0) {
    while ($row = $result_carousel->fetch_assoc()) {
        $carouselItems[] = $row;
    }
}

// Buscar as 3 últimas notícias para a lista
$sql_latest = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 3";
$result_latest = $conn->query($sql_latest);

$latestNews = [];

if ($result_latest->num_rows > 0) {
    while ($row = $result_latest->fetch_assoc()) {
        $latestNews[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEMUTRAN - Departamento Municipal de Trânsito Pau dos Ferros</title>
    <link rel="icon" href="./assets/icon.png" type="image/png" />
    <link rel="icon" href="./icon.png" type="image/png">

    <meta name="description"
        content="Site oficial do DEMUTRAN Pau dos Ferros. Encontre informações sobre regulamentação de trânsito, multas, infrações, e serviços online para facilitar sua vida.">
    <meta name="keywords"
        content="DEMUTRAN, Pau dos Ferros, Trânsito, Infrações, Multas, Licenciamento, Zona Azul, Regulamentação de Trânsito, Serviços Online, Segurança no Trânsito">
    <meta name="author" content="Departamento Municipal de Trânsito - Pau dos Ferros">

    <meta property="og:title" content="Demutran Pau dos Ferros">
    <meta property="og:description"
        content="Conheça os serviços do DEMUTRAN em Pau dos Ferros, incluindo consulta de multas, licenciamento e regulamentação de trânsito.">
    <meta property="og:image" content="./assets/prefeitura-logo.png">
    <meta property="og:url" content="https://www.detran.paudosferros.gov.br">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DEMUTRAN - Departamento Municipal de Trânsito Pau dos Ferros">
    <meta name="twitter:description"
        content="Acesse os serviços de trânsito de Pau dos Ferros, consulte multas e obtenha mais informações sobre o tráfego local.">
    <meta name="twitter:image" content="./assets/prefeitura-logo.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Flowbite -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
</head>

<style>
.square-image {
    position: relative;
    width: 100%;
    padding-bottom: 100%;
    overflow: hidden;
}

.square-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
</head>

<body class="font-roboto bg-gray-100">
    <!-- Hero Section -->
    <div class="bg-white border-b">
        <!-- Top Bar fixo -->
        <header class="bg-green-600 text-white w-full fixed top-0 left-0 z-50">
            <div class="container mx-auto px-4 py-3 flex justify-between items-center">
                <!-- Logo -->
                <div class="text-lg font-semibold flex items-center">
                    <img src="./assets/icon.png" alt="DEMUTRAN" class="h-8 w-8 mr-2">
                    <a class="hover:text-green-300">DEMUTRAN</a>
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
                <a href="../PCD/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Cartão Vaga
                    Especial</a>
                <a href="../Defesa/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Defesa
                    Prévia/JARI</a>
                <a href="../DAT/index.php" class="block px-4 py-2 text-white hover:bg-green-500">DAT</a>
                <a href="../contato/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Contato</a>
            </div>
        </header>

        <!-- Hero Section Reorganizada -->
        <div class="container mx-auto pt-24 pb-12 px-4">
            <!-- Cabeçalho com Logos -->
            <div class="flex flex-col items-center justify-center space-y-8">
                <!-- Logos principais em uma linha -->
                <div class="flex flex-wrap justify-center items-center gap-8 md:gap-16">
                    <img src="utils/form/image1.png" alt="DEMUTRAN Logo" class="h-24 md:h-28 object-contain">
                    <img src="admin/assets/logo vazada horizontal.png" alt="Logo Demutran"
                        class="h-24 md:h-28 object-contain">
                    <img src="./assets/prefeitura-logo.png" alt="Prefeitura Logo" class="h-24 md:h-28 object-contain">
                </div>

                <!-- Título e Subtítulo -->
                <div class="text-center max-w-3xl mx-auto">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">
                        DEPARTAMENTO MUNICIPAL DE TRÂNSITO
                    </h1>
                    <p class="text-xl text-gray-600 mb-4">
                        Pau dos Ferros/RN
                    </p>
                    <div class="text-sm text-gray-600 bg-gray-50 p-4 rounded-lg">
                        <p class="mb-2">
                            Integrado ao Sistema Nacional de Trânsito<br>
                            Portaria nº 150/2022 SENATRAN<br>
                            Código do órgão autuador: 21787-0
                        </p>
                    </div>
                </div>

                <!-- Informação Legal em Card -->
                <div class="bg-white shadow-sm rounded-lg p-6 max-w-3xl w-full">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold block mb-2">LEI N° 1129/2014, DE 07 DE MAIO DE 2014</span>
                        Dispõe sobre a criação do Departamento Municipal de Trânsito, órgão executivo de trânsito
                        rodoviário,
                        vinculado ao Gabinete do Município, bem como da Junta Administrativa de Recursos de Infração –
                        JARI.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Conteúdo Principal -->
    <main class="container mx-auto px-4 py-8">
        <!-- Seção de Notícias -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Notícia Principal com Carrossel -->
            <div class="lg:col-span-2">
                <div id="default-carousel" class="relative w-full" data-carousel="slide">
                    <!-- Wrapper do Carrossel - Mantendo 4:3 -->
                    <div class="relative w-full h-0 pb-[66.67%] overflow-hidden rounded-lg">
                        <?php foreach ($carouselItems as $index => $item): ?>
                        <div class="hidden duration-700 ease-in-out absolute inset-0" data-carousel-item>
                            <img src="<?php echo $item['imagem_url']; ?>"
                                alt="<?php echo htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="absolute block w-full h-full object-cover">
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Indicadores -->
                    <div class="absolute z-30 flex -translate-x-1/2 space-x-3 rtl:space-x-reverse bottom-5 left-1/2">
                        <?php foreach ($carouselItems as $index => $item): ?>
                        <button type="button" class="w-3 h-3 rounded-full bg-white/50 hover:bg-white"
                            aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                            aria-label="Slide <?php echo $index + 1; ?>" data-carousel-slide-to="<?php echo $index; ?>">
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Controles -->
                    <button type="button"
                        class="absolute top-0 start-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                        data-carousel-prev>
                        <span
                            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                            <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M5 1 1 5l4 4" />
                            </svg>
                            <span class="sr-only">Anterior</span>
                        </span>
                    </button>
                    <button type="button"
                        class="absolute top-0 end-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                        data-carousel-next>
                        <span
                            class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                            <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 9 4-4-4-4" />
                            </svg>
                            <span class="sr-only">Próximo</span>
                        </span>
                    </button>
                </div>
            </div>

            <!-- Barra Lateral de Notícias -->
            <div class="flex flex-col justify-between h-full">
                <?php foreach ($latestNews as $news): ?>
                <a href="noticia.php?id=<?php echo $news['id']; ?>"
                    class="flex bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                    <div class="relative w-[200px] h-[150px] flex-shrink-0">
                        <!-- Proporção 4:3 fixa -->
                        <img src="<?php echo $news['imagem_url']; ?>"
                            alt="<?php echo htmlspecialchars($news['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                            class="absolute inset-0 w-full h-full object-cover rounded-l-lg"
                            onerror="this.onerror=null; this.src='./assets/placeholder.jpg';" loading="lazy">
                    </div>
                    <div class="flex flex-col justify-between p-4 flex-grow">
                        <h3 class="text-lg font-bold text-gray-900 line-clamp-2">
                            <?php echo htmlspecialchars($news['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                        <p class="text-sm text-gray-600 line-clamp-2 mt-2">
                            <?php echo htmlspecialchars($news['resumo'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="./todas-noticias.php"
                class="inline-block bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700">
                Ver todas as notícias
            </a>
        </div>
        <!-- Informações Adicionais -->
        <p class="text-gray-600 text-center max-w-4xl mx-auto mt-8">
            <strong>DE ACORDO COM A RESOLUÇÃO CONTRAN Nº 900, DE 9 DE MARÇO DE 2022,</strong>
            consolida as normas sobre a padronização dos procedimentos para
            apresentação de defesa prévia e de recurso, em 1ª e 2ª instâncias,
            contra a imposição de penalidades de advertência por escrito e de multa
            de trânsito.
        </p>
    </main>

    <!-- Seção de Opções -->
    <section class="container mx-auto px-4 py-8">
        <!-- Grid principal -->
        <div class="grid grid-cols-1 gap-6">
            <!-- Linha superior - 3 cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <a href="./Defesa/index.html" class="h-full">
                    <div
                        class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 h-full flex flex-col items-center justify-center text-center">
                        <i class="fas fa-file-alt text-green-600 text-4xl mb-4"></i>
                        <span class="block text-lg font-semibold text-gray-800">Defesa Prévia/JARI e Apresentação de
                            Condutor Infrator</span>
                    </div>
                </a>

                <a href="./PCD/index.html" class="h-full">
                    <div
                        class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 h-full flex flex-col items-center justify-center text-center">
                        <i class="fas fa-id-card text-green-600 text-4xl mb-4"></i>
                        <span class="block text-lg font-semibold text-gray-800">Cartão Vaga Especial
                            PCD/Idoso</span>
                    </div>
                </a>

                <a href="./contato/index.html" class="h-full">
                    <div
                        class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 h-full flex flex-col items-center justify-center text-center">
                        <i class="fas fa-phone text-green-600 text-4xl mb-4"></i>
                        <span class="block text-lg font-semibold text-gray-800">Sugestões / Reclamações</span>
                    </div>
                </a>
            </div>

            <!-- Linha inferior - 2 cards largos -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <a href="./DAT/index.php" class="h-full">
                    <div
                        class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition-shadow duration-300 h-full flex flex-col items-center justify-center text-center">
                        <i class="fas fa-car-crash text-green-600 text-5xl mb-4"></i>
                        <span class="block text-xl font-semibold text-gray-800">Sistema de Declaração de Acidente de
                            Trânsito - DAT</span>
                    </div>
                </a>

                <a href="./Parecer/index.php" class="h-full">
                    <div
                        class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition-shadow duration-300 h-full flex flex-col items-center justify-center text-center">
                        <i class="fas fa-clipboard-list text-green-600 text-5xl mb-4"></i>
                        <span class="block text-xl font-semibold text-gray-800">Solicitação de Parecer -
                            DEMUTRAN</span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Botão Voltar ao Topo -->
    <div class="text-center mt-8">
        <a href="#top" class="inline-flex items-center text-green-600 hover:text-green-800">
            <i class="fas fa-arrow-up mr-2"></i>
            <span>Voltar ao Topo</span>
        </a>
    </div>

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
                <p>demutranpmpf@gmail.com</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-2">ENDEREÇO E HORÁRIO</h2>
                <p>
                    Av. Dom Pedro II, 1121 Centro, CEP 59900-000
                    Pau dos Ferros-RN
                </p>
                <p>SEGUNDA A SEXTA-FEIRA, DAS 7H ÀS 13H</p>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-2">REDES SOCIAIS</h2>
                <a href="https://www.instagram.com/demutran_paudosferros/"><i class="fab fa-instagram text-2xl"></i></a>
                <a href="https://wa.me/558499440704"><i class="fab fa-whatsapp text-2xl"></i></a>
            </div>
        </div>
        </div>

    </footer>
    <a href="https://www.youtube.com/c/prefeituramunicipaldepaudosferros"><i </footer>

            <!-- Scripts -->
            <!-- Swiper JS -->
            <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

            <!-- JavaScript do Carrossel -->
            <script>
            // Mobile Menu Toggle
            const menuBtn = document.getElementById("menu-btn");
            const mobileMenu = document.getElementById("mobile-menu");

            menuBtn.addEventListener("click", () => {
                mobileMenu.classList.toggle("hidden");
            });

            // Evento para o Select de Formulários
            document
                .getElementById("pageSelect")
                .addEventListener("change", function() {
                    var selectedPage = this.value;
                    if (selectedPage) {
                        window.open(selectedPage, "_blank");
                    }
                });

            // Swiper Initialization
            var swiper = new Swiper(".swiper-container", {
                loop: true,
                autoplay: {
                    delay: 5000,
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                slidesPerView: 1,
                spaceBetween: 10,
            });
            </script>
</body>

</html>