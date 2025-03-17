<?php
require_once(__DIR__ . '/../includes/header.php');
?>

<div class="pt-20 flex justify-center flex-col items-center">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full">
            <div class="text-center">
                <div class="mb-6">
                    <svg class="mx-auto h-20 w-20 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Sistema em Manutenção
                </h2>

                <div class="max-w-lg mx-auto mb-6">
                    <div class="bg-gray-100 h-2.5 rounded-full mb-2">
                        <div class="bg-green-500 h-2.5 rounded-full w-3/4 animate-pulse"></div>
                    </div>
                    <p class="text-xs text-gray-500">Progresso estimado: 75%</p>
                </div>

                <div class="mb-8 text-gray-600">
                    <p class="mb-4">
                        Estamos realizando melhorias no sistema para proporcionar uma experiência ainda melhor.
                    </p>
                    <p class="mb-4">
                        Por favor, tente novamente mais tarde. Agradecemos sua compreensão.
                    </p>
                    <!-- <p class="text-sm text-gray-500">
                        Previsão de retorno: <span class="font-semibold">18:00h</span>
                    </p> -->
                </div>

                <div class="flex justify-center gap-4">
                    <a href="../../index.php" class="bg-green-500 text-white px-5 py-2 rounded hover:bg-green-600 transition-colors">
                        Voltar à página inicial
                    </a>
                    <a href="../../contato/index.html" class="bg-gray-200 text-gray-700 px-5 py-2 rounded hover:bg-gray-300 transition-colors">
                        Contato
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Se precisar de assistência imediata, entre em contato com o suporte através do e-mail:
                <a href="mailto:demutranpmpf@gmail.com" class="text-green-600 hover:underline">suporte@demutran.com.br</a>
            </p>
        </div>
    </div>
</div>

<?php
require_once(__DIR__ . '/../includes/footer.php');
?>