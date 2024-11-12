<?php
// index.php
include 'admin/config.php';

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
<<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEMUTRAN - Departamento Municipal de Trânsito Pau dos Ferros</title>
    <link rel="icon" href="./assets/icon.png" type="image/png" />

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
    <!-- Swiper CSS para o carrossel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
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
        <!-- Início da Página -->
        <div id="top"></div>

        <!-- Top Bar -->
        <header class="bg-green-600 text-white shadow-md w-full fixed top-0 left-0 z-50">
            <div class="container mx-auto px-4 py-3 flex justify-between items-center">
                <!-- Logo -->
                <div class="text-lg font-semibold">
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

        <!-- Topbar Secundária -->
        <div class="bg-gray-200 border-b border-gray-300 mt-16">
            <div class="container mx-auto py-2 text-center">
                <a href="https://paudosferros.rn.gov.br/secretaria.php?sec=19"
                    class="text-gray-800 hover:text-gray-600">Demutran – DEPARTAMENTO MUNICIPAL DE TRÂNSITO</a>
                <span class="text-gray-600 mx-2">|</span>
                <a href="https://paudosferros.rn.gov.br/lai.php" class="text-gray-800 hover:text-gray-600">Acesso à
                    Informação</a>
                <span class="text-gray-600 mx-2">-</span>
                <a href="https://paudosferros.rn.gov.br/omunicipio.php" class="text-gray-800 hover:text-gray-600">Sobre
                    a Prefeitura</a>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <main class="container mx-auto py-8">
            <h1 class="text-4xl font-bold text-center text-gray-800 mb-6">
                DEMUTRAN – DEPARTAMENTO MUNICIPAL DE TRÂNSITO
            </h1>
            <p class="text-gray-600 text-center max-w-4xl mx-auto mb-8">
                <strong>LEI N° 1129/2014, DE 07 DE MAIO DE 2014.</strong> DISPÕE SOBRE A
                CRIAÇÃO DO DEPARTAMENTO MUNICIPAL DE TRÂNSITO, ÓRGÃO EXECUTIVO DE
                TRÂNSITO RODOVIÁRIO, VINCULADO AO GABINETE DO MUNICÍPIO, BEM COMO DA
                JUNTA ADMINISTRATIVA DE RECURSOS DE INFRAÇÃO – JARI.
            </p>

            <!-- Seção de Notícias -->
            <div class="flex flex-wrap items-start">
                <!-- Notícia Principal com Carrossel -->
                <div class="w-full md:w-2/3 p-4">
                    <div class="relative">
                        <!-- Slides -->
                        <?php foreach ($carouselItems as $index => $item): ?>
                        <div class="slide <?php if ($index !== 0) echo 'hidden'; ?>">
                            <div class="aspect-w-16 aspect-h-9">
                                <img src="<?php echo $item['imagem_url']; ?>"
                                    alt="<?php echo htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="object-cover w-full h-full rounded-lg" />
                            </div>
                            <div class="absolute bottom-0 left-0 bg-black bg-opacity-50 text-white p-4 rounded-b-lg">
                                <?php echo htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Botões de Navegação -->
                        <button
                            class="absolute top-1/2 left-0 transform -translate-y-1/2 text-white text-3xl p-2 focus:outline-none"
                            onclick="changeSlide(-1)">
                            &#10094;
                        </button>
                        <button
                            class="absolute top-1/2 right-0 transform -translate-y-1/2 text-white text-3xl p-2 focus:outline-none"
                            onclick="changeSlide(1)">
                            &#10095;
                        </button>
                    </div>
                </div>


                <!-- Barra Lateral de Notícias -->
                <div class="w-full md:w-1/3 p-4">
                    <div class="space-y-6">
                        <?php foreach ($latestNews as $news): ?>
                        <div class="flex bg-white shadow-lg rounded-lg overflow-hidden">
                            <div class="w-1/3">
                                <img src="<?php echo $news['imagem_url']; ?>"
                                    alt="<?php echo htmlspecialchars($news['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="object-cover w-full h-full">
                            </div>
                            <div class="w-2/3 p-4">
                                <h3 class="text-gray-900 font-bold text-lg">
                                    <?php echo htmlspecialchars($news['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="text-gray-600 text-sm">
                                    <?php echo htmlspecialchars($news['resumo'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="mt-2">
                                    <a href="noticia.php?id=<?php echo $news['id']; ?>"
                                        class="text-green-600 hover:underline text-sm">Leia mais</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="todas-noticias.php"
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
        <section class="container mx-auto py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <!-- Opção 1 -->
                <div class="option hover:shadow-lg transition duration-300 p-4 bg-white rounded-lg cursor-pointer relative"
                    onclick="toggleDropdown()">
                    <i class="fas fa-file-download text-green-600 text-4xl mb-4"></i>
                    <span class="block text-lg font-semibold">Formulários</span>

                    <!-- Custom Dropdown Menu -->
                    <div id="dropdownMenu"
                        class="hidden absolute left-0 right-0 mt-2 bg-white border border-gray-200 rounded shadow-lg z-10">
                        <a href="./assets/forms/APRESENTAÇÃO DE RECURSO A JARI.docx.pdf" target="_blank"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">APRESENTAÇÃO DE RECURSO A JARI</a>
                        <a href="./assets/forms/APRESENTAÇÃO DE DEFESA PRÉVIA.docx.pdf" target="_blank"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">APRESENTAÇÃO DE DEFESA PRÉVIA</a>
                        <a href="./assets/forms/PARECER E SOLICITAÇÃO.docx.pdf" target="_blank"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">PARECER E SOLICITAÇÃO</a>
                    </div>
                </div>
                <!-- Opção 2 -->
                <a href="./Defesa/index.html">
                    <div class="option hover:shadow-lg transition duration-300 p-4 bg-white rounded-lg">
                        <i class="fas fa-file-alt text-green-600 text-4xl mb-4"></i>
                        <span class="block text-lg font-semibold">Faça Sua Defesa Prévia/JARI</span>
                    </div>
                </a>
                <!-- Opção 3 -->
                <a href="./PCD/index.html">
                    <div class="option hover:shadow-lg transition duration-300 p-4 bg-white rounded-lg">
                        <i class="fas fa-id-card text-green-600 text-4xl mb-4"></i>
                        <span class="block text-lg font-semibold">Cartão Vaga Especial PCD/Idoso</span>
                    </div>
                </a>
                <!-- Opção 4 -->
                <a href="./contato/index.html">
                    <div class="option hover:shadow-lg transition duration-300 p-4 bg-white rounded-lg">
                        <i class="fas fa-phone text-green-600 text-4xl mb-4"></i>
                        <span class="block text-lg font-semibold">Informações de Contatos</span>
                    </div>
                </a>
                <!-- Opção 5 -->
                <a href="./DAT/index.php">
                    <div class="option hover:shadow-lg transition duration-300 p-4 bg-white rounded-lg">
                        <i class="fas fa-car-crash text-green-600 text-4xl mb-4"></i>
                        <span class="block text-lg font-semibold">Sistema de Declaração de Acidente de Trânsito -
                            DAT</span>
                    </div>
                </a>
                <a href="./Parecer/index.php">
                    <div class="option hover:shadow-lg transition duration-300 p-4 bg-white rounded-lg">
                        <i class="fas fa-clipboard-list text-green-600 text-4xl mb-4"></i>
                        <span class="block text-lg font-semibold">
                            Solicitação de Parecer - DEMUTRAN Pau dos Ferros
                        </span>
                    </div>
                </a>

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
                    <a href="https://www.instagram.com/demutran_paudosferros/"><i
                            class="fab fa-instagram text-2xl"></i></a>
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
                let slideIndex = 0;
                showSlides();

                function showSlides() {
                    let slides = document.getElementsByClassName("slide");
                    if (slides.length === 0) return; // Verifica se há slides disponíveis
                    for (let i = 0; i < slides.length; i++) {
                        slides[i].classList.add("hidden");
                    }
                    slideIndex++;
                    if (slideIndex > slides.length) {
                        slideIndex = 1;
                    }
                    slides[slideIndex - 1].classList.remove("hidden");
                    setTimeout(showSlides, 5000); // Muda a imagem a cada 5 segundos
                }


                function changeSlide(n) {
                    let slides = document.getElementsByClassName("slide");
                    slideIndex += n;
                    if (slideIndex > slides.length) {
                        slideIndex = 1;
                    }
                    if (slideIndex < 1) {
                        slideIndex = slides.length;
                    }
                    for (let i = 0; i < slides.length; i++) {
                        slides[i].classList.add("hidden");
                    }
                    slides[slideIndex - 1].classList.remove("hidden");
                }
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

                function toggleDropdown() {
                    var dropdown = document.getElementById('dropdownMenu');
                    dropdown.classList.toggle('hidden');
                }

                // Close dropdown when clicking outside
                window.onclick = function(event) {
                    var dropdown = document.getElementById('dropdownMenu');
                    if (!event.target.closest('.option')) {
                        dropdown.classList.add('hidden');
                    }
                }
                </script>
    </body>

</html>