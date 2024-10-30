<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Defesa Prévia DEMUTRAN</title>
    <!-- Inclua o Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inclua o Bootstrap para modais, se necessário -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-gray-100">
    <!-- Topbar -->
    <header class="bg-green-600 text-white shadow-md w-full fixed top-0 left-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Logo -->
            <div class="text-lg font-semibold">
                <a href="../" class="hover:text-green-300">DEMUTRAN</a>
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

    <!-- Conteúdo Principal -->
    <div class="pt-24 flex justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
            <div id="feedback" class="hidden"></div>

            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                SOLICITAÇÃO DE PARECER AO DEPARTAMENTO MUNICIPAL DE TRÂNSITO – DEMUTRAN/PAU DOS FERROS </h2>
            <p class="text-gray-600 mb-6">
                <strong>Preencha todos os dados para podermos dar continuidade à sua solicitação.</strong>
            </p>

            <form id="defesaForm" method="post" enctype="multipart/form-data">
                <!-- Nome -->
                <div class="mb-4">
                    <label for="nome" class="block text-gray-700 mb-2">
                        NOME DO SOLICITANTE:
                    </label>
                    <input type="text" id="nome" name="nome" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Telefone -->
                <div class="mb-4">
                    <label for="telefone" class="block text-gray-700 mb-2">
                        Nº TELEFONE:
                    </label>
                    <input type="tel" id="telefone" name="telefone" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">
                        CPF/CNPJ:
                    </label>
                    <input type="text" id="email" name="email" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Assunto -->
                <div class="mb-4">
                    <label for="assunto" class="block text-gray-700 mb-2">
                        LOCAL:
                    </label>
                    <input type="text" id="assunto" name="assunto" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="mb-4">
                    <label for="assunto" class="block text-gray-700 mb-2">
                        evento:
                    </label>
                    <input type="text" id="assunto" name="assunto" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="mb-4">
                    <label for="assunto" class="block text-gray-700 mb-2">
                        ponto de referencia:
                    </label>
                    <input type="text" id="assunto" name="assunto" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="mb-4">
                    <label for="assunto" class="block text-gray-700 mb-2">
                        Data/horario:
                    </label>
                    <input type="text" id="assunto" name="assunto" required
                        class="w-full border rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                    Enviar
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inclua o Bootstrap JS para modais (se necessário) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Script para alternar o menu mobile
    const menuBtn = document.getElementById("menu-btn");
    const mobileMenu = document.getElementById("mobile-menu");

    menuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
    });

    // Envio do formulário
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("defesaForm");
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            submitForm();
        });
    });

    function submitForm() {
        const form = document.getElementById("defesaForm");
        const formData = new FormData(form);

        // Verificação dos arquivos PDF
        for (let [key, value] of formData.entries()) {
            if (value instanceof File && value.name !== "" && value.type !== "application/pdf") {
                alert("Todos os arquivos devem estar no formato PDF.");
                return;
            }
        }

        fetch("processa_defesa.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const feedback = document.getElementById("feedback");
                feedback.innerHTML = data;
                feedback.classList.add("bg-green-100", "text-green-700", "p-4", "rounded", "mb-4");
                feedback.classList.remove("hidden");

                setTimeout(() => {
                    feedback.classList.add("hidden");
                }, 5000);
            })
            .catch(error => {
                const feedback = document.getElementById("feedback");
                feedback.innerHTML = "Ocorreu um erro ao enviar o formulário.";
                feedback.classList.add("bg-red-100", "text-red-700", "p-4", "rounded", "mb-4");
                feedback.classList.remove("hidden");

                setTimeout(() => {
                    feedback.classList.add("hidden");
                }, 5000);
            });
    }
    </script>
</body>

</html>