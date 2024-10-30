<header class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
    <div class="flex items-center space-x-3">
        <!-- Mobile Menu Button -->
        <button @click="open = !open" class="md:hidden focus:outline-none">
            <span class="material-icons">menu</span>
        </button>
        <h2 class="text-xl font-semibold text-gray-800">
            <?php echo isset($titulo_pagina) ? $titulo_pagina : 'Painel Administrativo'; ?>
        </h2>
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
            <!-- Notificações dropdown -->
            <!-- ... -->
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
