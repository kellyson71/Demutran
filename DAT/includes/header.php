<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Multi-step Form - DEMUTRAN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../icon.png" type="image/png" />
    <style>
        .step {
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }

        .step.hidden {
            opacity: 0;
            transform: translateX(20px);
            pointer-events: none;
        }

        .progress-bar {
            transition: width 0.4s ease-in-out;
        }

        .progress-container {
            overflow: hidden;
        }

        /* Estilos globais que precisam persistir */
        header.bg-green-600,
        #mobile-menu {
            background-color: #16A34A !important;
            /* Verde original */
        }

        header a,
        header .text-white {
            color: #ffffff !important;
            text-decoration: none !important;
        }

        header a:hover,
        header nav a:hover,
        .hover\:text-green-300:hover {
            color: #BBF7D0 !important;
            /* Verde claro para hover */
        }

        header nav a {
            color: #ffffff !important;
            text-decoration: none !important;
            transition: color 0.2s ease-in-out;
        }

        header nav a:hover {
            color: #BBF7D0 !important;
            /* Verde claro para hover */
        }

        /* Força estilos do logo */
        header .font-semibold a {
            color: #ffffff !important;
            text-decoration: none !important;
        }

        /* Força estilos no menu mobile */
        #mobile-menu a {
            color: #ffffff !important;
            text-decoration: none !important;
        }

        #mobile-menu a:hover {
            background-color: #15803D !important;
            /* Verde escuro para hover do menu mobile */
        }

        /* Previne estilos padrão de links afetarem a navegação */
        .hover\:text-green-300:hover {
            color: #BBF7D0 !important;
            /* Verde claro para hover */
        }

        /* Garante que os estilos do checkbox permaneçam */
        .step .form-checkbox {
            appearance: none !important;
            -webkit-appearance: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100">
    <!-- Toast de Progresso -->
    <div id="toast-progress"
        class="fixed top-24 right-4 z-50 hidden flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800"
        role="alert">
        <div
            class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
            </svg>
        </div>
        <div class="ms-3 text-sm font-normal" id="toast-message"></div>
        <button type="button"
            class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8"
            onclick="closeToast()">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
            </svg>
        </button>
    </div>

    <!-- Topbar -->
    <header class="bg-green-600 text-white shadow-md w-full fixed top-0 left-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Logo -->
            <div class="text-lg font-semibold flex items-center">
                <img src="../../assets/icon.png" alt="DEMUTRAN" class="h-8 w-8 mr-2" />
                <a href="../../" class="hover:text-green-300">DEMUTRAN</a>
            </div>

            <!-- Links -->
            <nav class="hidden md:flex space-x-6">
                <a href="../../PCD/index.html" class="hover:text-green-300">Cartão Vaga Especial</a>
                <a href="../../Defesa/index.html" class="hover:text-green-300">Defesa Prévia/JARI</a>
                <a href="../../DAT/escolher-dat.html" class="hover:text-green-300">DAT</a>
                <a href="../../Parecer/index.php" class="hover:text-green-300">Parecer</a>
                <a href="../../contato/index.html" class="hover:text-green-300">Contato</a>
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
            <a href="../../PCD/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Cartão Vaga
                Especial</a>
            <a href="../../Defesa/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Defesa
                Prévia/JARI</a>
            <a href="../../DAT/escolher-dat.html" class="block px-4 py-2 text-white hover:bg-green-500">DAT</a>
            <a href="../../Parecer/index.php" class="block px-4 py-2 text-white hover:bg-green-500">Parecer</a>
            <a href="../../contato/index.html" class="block px-4 py-2 text-white hover:bg-green-500">Contato</a>
        </div>
    </header>
</body>

</html>
<?php
ob_end_flush();
?>