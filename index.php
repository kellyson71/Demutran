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

// Buscar todas as notícias para o carrossel (apenas publicadas)
$sql_carousel = "SELECT * FROM noticias 
                WHERE data_publicacao <= CURDATE() 
                ORDER BY data_publicacao DESC";
$result_carousel = $conn->query($sql_carousel);

$carouselItems = [];

if ($result_carousel->num_rows > 0) {
    while ($row = $result_carousel->fetch_assoc()) {
        $carouselItems[] = $row;
    }
}

// Buscar as 3 últimas notícias para a lista (apenas publicadas)
$sql_latest = "SELECT * FROM noticias 
               WHERE data_publicacao <= CURDATE() 
               ORDER BY data_publicacao DESC 
               LIMIT 3";
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
    <!-- Hero Section com background enriquecido -->
    <div class="relative bg-gradient-to-br from-green-50 via-gray-50 to-white">
        <!-- Elementos decorativos de fundo -->
        <div class="absolute inset-0 overflow-hidden">
            <!-- Padrão de grid principal -->
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>

            <!-- Elementos decorativos flutuantes -->
            <div class="absolute inset-0">
                <!-- Círculos decorativos -->
                <div
                    class="absolute top-20 left-10 w-64 h-64 bg-green-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse">
                </div>
                <div
                    class="absolute top-40 right-20 w-72 h-72 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse-slow">
                </div>
                <div
                    class="absolute -bottom-20 left-1/3 w-96 h-96 bg-yellow-50 rounded-full mix-blend-multiply filter blur-3xl opacity-30">
                </div>

                <!-- Padrões geométricos -->
                <div class="absolute inset-0 bg-texture opacity-5"></div>
                <div class="absolute inset-0 bg-dots opacity-10"></div>

                <!-- Linhas decorativas -->
                <div
                    class="absolute top-1/4 left-0 w-full h-px bg-gradient-to-r from-transparent via-green-200 to-transparent opacity-30">
                </div>
                <div
                    class="absolute top-3/4 left-0 w-full h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent opacity-30">
                </div>

                <!-- Formas geométricas flutuantes -->
                <div
                    class="absolute top-10 right-1/4 w-20 h-20 border-2 border-green-200 rounded-lg transform rotate-45 opacity-20">
                </div>
                <div class="absolute bottom-20 left-1/4 w-16 h-16 border-2 border-blue-200 rounded-full opacity-20">
                </div>
                <div
                    class="absolute top-1/3 right-10 w-24 h-24 border-2 border-yellow-200 transform rotate-12 opacity-20">
                </div>
            </div>
        </div>

        <!-- Estilos adicionais -->
        <style>
        .bg-grid-pattern {
            background-image:
                linear-gradient(to right, rgba(0, 128, 0, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 128, 0, 0.05) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .bg-texture {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23green' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .bg-dots {
            background-image: radial-gradient(circle, rgba(0, 128, 0, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 0.2;
                transform: scale(1);
            }

            50% {
                opacity: 0.3;
                transform: scale(1.05);
            }
        }

        .animate-pulse-slow {
            animation: pulse-slow 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Animações para formas geométricas */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(45deg);
            }

            50% {
                transform: translateY(-10px) rotate(45deg);
            }
        }

        .absolute[class*="border-"] {
            animation: float 15s ease-in-out infinite;
        }
        </style>

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

        <!-- Conteúdo com padding-top para compensar a topbar fixa -->
        <div class="relative pt-14">
            <!-- Faixa Institucional (não fixa) -->
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white border-b border-gray-700">
                <div class="container mx-auto">
                    <div class="flex flex-col md:flex-row justify-between items-center py-2 px-4">
                        <div class="flex items-center space-x-6">
                            <a href="https://paudosferros.rn.gov.br" target="_blank"
                                class="flex items-center space-x-3 hover:text-green-300 transition-colors">
                                <img src="admin/assets/prefeitura-logo.png" alt="Brasão de Pau dos Ferros"
                                    class="h-8 w-auto">
                                <div class="text-left">
                                    <p class="font-semibold text-sm">Prefeitura Municipal de</p>
                                    <p class="text-base">Pau dos Ferros</p>
                                </div>
                            </a>
                            <div class="hidden md:block h-6 w-px bg-gray-700"></div>
                            <div class="hidden md:block text-xs text-gray-300">
                                <p>Rio Grande do Norte</p>
                                <p>CNPJ: 08.148.421/0001-76</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-6">
                            <a href="https://www.instagram.com/demutran_paudosferros/" target="_blank"
                                class="hover:text-green-300 transition-colors flex items-center space-x-2">
                                <i class="fab fa-instagram"></i>
                                <span class="text-xs hidden md:inline">Instagram</span>
                            </a>
                            <div class="h-6 w-px bg-gray-700"></div>
                            <a href="https://paudosferros.rn.gov.br/acessoainformacao.php" target="_blank"
                                class="hover:text-green-300 transition-colors">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-search text-xs"></i>
                                    <span class="text-xs">Portal da Transparência</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Section Reformulada -->
            <div class="container mx-auto py-12 px-4">
                <div class="max-w-7xl mx-auto">
                    <div class="grid md:grid-cols-2 gap-12 items-center">
                        <!-- Conteúdo Principal -->
                        <div class="space-y-8">
                            <div class="space-y-4">
                                <div class="flex items-center space-x-3 mb-6">
                                    <span
                                        class="px-4 py-2 rounded-full bg-green-50 border border-green-100 text-green-700 text-sm font-medium">
                                        Sistema Municipal de Trânsito
                                    </span>
                                    <span
                                        class="px-4 py-2 rounded-full bg-gray-50 border border-gray-100 text-gray-700 text-sm font-medium">
                                        Pau dos Ferros/RN
                                    </span>
                                </div>
                                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight">
                                    Departamento Municipal de Trânsito
                                </h1>
                                <p class="text-xl text-gray-600 leading-relaxed max-w-2xl">
                                    Órgão executivo municipal responsável pela gestão e fiscalização do trânsito,
                                    trabalhando por uma mobilidade mais segura e eficiente.
                                </p>
                            </div>

                            <!-- Cards de Credenciais -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-green-50 rounded-full">
                                            <i class="fas fa-building text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Órgão Oficial</p>
                                            <p class="text-xs text-gray-500">Executivo Municipal</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-green-50 rounded-full">
                                            <i class="fas fa-file-contract text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Portaria SENATRAN</p>
                                            <p class="text-xs text-gray-500">Nº 150/2022</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-green-50 rounded-full">
                                            <i class="fas fa-id-card text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Código Autuador</p>
                                            <p class="text-xs text-gray-500">21787-0</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informação Adicional -->
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center space-x-4">
                                    <img src="./assets/icon.png" alt="Logo DEMUTRAN" class="h-12 w-12">
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        Responsável pela gestão do trânsito municipal e pela Junta Administrativa de
                                        Recursos de
                                        Infração (JARI),
                                        atuando em conformidade com as diretrizes do Sistema Nacional de Trânsito para
                                        garantir
                                        um trânsito mais seguro e organizado.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Imagem Principal -->
                        <div class="relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-green-600 to-green-400 rounded-3xl opacity-10 blur-3xl transform rotate-6">
                            </div>
                            <div class="relative bg-white p-8 rounded-2xl shadow-xl">
                                <a href="https://paudosferros.rn.gov.br" target="_blank"
                                    class="block transition-transform duration-500 hover:scale-105">
                                    <img src="admin/assets/logo vazada horizontal.png"
                                        alt="Brasão da Prefeitura Municipal de Pau dos Ferros" class="w-full h-auto"
                                        title="Prefeitura Municipal de Pau dos Ferros">
                                </a>
                                <div class="text-center mt-6">
                                    <p class="text-lg font-semibold text-gray-800">Prefeitura Municipal de Pau dos
                                        Ferros
                                    </p>
                                    <p class="text-sm text-gray-600">Administração 2021-2024</p>
                                </div>
                            </div>
                        </div>

                    </div>
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
                <?php if (count($carouselItems) > 0): ?>
                <div id="default-carousel" class="relative w-full" data-carousel="slide">
                    <!-- Wrapper do Carrossel - Mantendo 4:3 -->
                    <div class="relative w-full h-0 pb-[66.67%] overflow-hidden rounded-lg">
                        <?php foreach ($carouselItems as $index => $item): ?>
                        <div class="hidden duration-700 ease-in-out absolute inset-0" data-carousel-item>
                            <img src="<?php echo get_image_path($item['imagem_url']); ?>"
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
                <?php else: ?>
                <div class="relative w-full h-0 pb-[66.67%] bg-gray-100 rounded-lg overflow-hidden">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                        <i class="fas fa-newspaper text-gray-400 text-5xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhuma notícia disponível</h3>
                        <p class="text-gray-500">Em breve publicaremos novidades aqui!</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Barra Lateral de Notícias -->
            <div class="flex flex-col justify-between h-full">
                <?php if (count($latestNews) > 0): ?>
                <?php foreach ($latestNews as $news): ?>
                <a href="noticia.php?id=<?php echo $news['id']; ?>"
                    class="flex bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                    <div class="relative w-[200px] h-[150px] flex-shrink-0">
                        <img src="<?php echo get_image_path($news['imagem_url']); ?>"
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
                <?php else: ?>
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow p-6 text-center h-full flex flex-col items-center justify-center">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">Nenhuma notícia recente</h3>
                    <p class="text-gray-500">Fique atento para as próximas atualizações!</p>
                </div>
                <?php endif; ?>
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
                <a href="./DAT/escolher-dat.html" class="h-full">
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
    <div class="text-center mt-8 space-y-4">
        <a href="#top" class="inline-flex items-center text-green-600 hover:text-green-800">
            <i class="fas fa-arrow-up mr-2"></i>
            <span>Voltar ao Topo</span>
        </a>
        <div class="text-gray-400 text-sm">
            <a href="./admin/login.php" class="hover:text-gray-600 transition-colors duration-300">
                <i class="fas fa-lock text-xs"></i>
                <span>Área Administrativa</span>
            </a>
        </div>
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