
<header class="bg-white shadow">
    <nav class="container mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <img src="../assets/images/logo.png" alt="Logo Demutran" class="h-12">
                <span class="ml-3 text-xl font-semibold text-gray-800">DEMUTRAN</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="index.php" class="text-gray-600 hover:text-gray-900">
                    <i class='bx bxs-dashboard'></i>
                    Dashboard
                </a>
                <a href="formularios.php" class="text-gray-600 hover:text-gray-900">
                    <i class='bx bx-file'></i>
                    Formulários
                </a>
                <div class="relative">
                    <button class="flex items-center text-gray-600 hover:text-gray-900">
                        <img src="<?php echo $_SESSION['usuario_avatar'] ?? '../assets/images/default-avatar.png'; ?>" 
                             alt="Avatar" 
                             class="h-8 w-8 rounded-full object-cover">
                        <span class="ml-2"><?php echo $_SESSION['usuario_nome'] ?? 'Usuário'; ?></span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
</header>