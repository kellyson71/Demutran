<?php
require_once './includes/formularios/form_list.php';
?>

<!DOCTYPE html>
<html lang="pt-BR" x-data="{ 
    open: false, 
    viewMode: '<?php echo $view_mode; ?>', 
    showUnreadOnly: <?php echo $apenas_nao_lidos ? 'true' : 'false'; ?> 
}" x-init="$refs.loading.classList.add('hidden');
    $watch('viewMode', value => {
        fetch('?view=' + value);
        localStorage.setItem('preferredView', value);
    });
    $watch('showUnreadOnly', value => {
        window.location.href = '?' + new URLSearchParams({
            ...Object.fromEntries(new URLSearchParams(window.location.search)),
            nao_lidos: value,
            pagina: 1
        }).toString();
    });">

<head>
    <meta charset="UTF-8">
    <title>Formulários Recebidos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Google Fonts (Roboto) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        [x-cloak] {
            display: none;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-completed {
            opacity: 0.75;
        }

        .card-completed:hover {
            opacity: 1;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            margin-left: 0.5rem;
        }
    </style>

    <!-- Script para filtros -->
    <script>
        function filtrar(tipo) {
            var cards = document.querySelectorAll('.form-card');
            cards.forEach(function(card) {
                if (tipo === 'todos' || card.dataset.tipo === tipo) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }
    </script>
</head>

<body class="bg-gray-100 font-roboto">
    <!-- Loader -->
    <div x-ref="loading" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
        <span class="material-icons animate-spin text-4xl text-blue-600">autorenew</span>
    </div>

    <!-- Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 flex flex-col h-full">
                <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                <?php echo getSidebarHtml('formularios'); ?>
                <div class="mt-6">
                    <a href="logout.php" class="flex items-center p-2 text-red-600 hover:bg-red-50 rounded">
                        <span class="material-icons">logout</span>
                        <span class="ml-3">Sair</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar -->
        <div x-show="open" @click.away="open = false" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden">
            <aside class="w-64 bg-white h-full shadow-md">
                <div class="p-6">
                    <h1 class="text-2xl font-bold text-blue-600 mb-6">Painel Admin</h1>
                    <nav class="space-y-2">
                        <a href="index.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">dashboard</span>
                            <span class="ml-3">Dashboard</span>
                        </a>
                        <a href="formularios.php" class="flex items-center p-2 text-gray-700 bg-blue-50 rounded">
                            <span class="material-icons">assignment</span>
                            <span class="ml-3 font-semibold">Formulários</span>
                        </a>
                        <a href="gerenciar_noticias.php"
                            class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">article</span>
                            <span class="ml-3">Notícias</span>
                        </a>
                        <a href="usuarios.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">people</span>
                            <span class="ml-3">Usuários</span>
                        </a>
                        <a href="perfil.php" class="flex items-center p-2 text-gray-700 hover:bg-blue-50 rounded">
                            <span class="material-icons">person</span>
                            <span class="ml-3">Perfil</span>
                        </a>
                        <a href="logout.php" class="flex items-center p-2 text-red-600 hover:bg-red-50 rounded">
                            <span class="material-icons">logout</span>
                            <span class="ml-3">Sair</span>
                        </a>
                    </nav>
                </div>
            </aside>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php
            $topbarHtml = getTopbarHtml('Formulários Recebidos', $notificacoesNaoLidas);
            $avatarHtml = getAvatarHtml($_SESSION['usuario_nome'], $_SESSION['usuario_avatar'] ?? '');
            echo str_replace('[AVATAR_PLACEHOLDER]', $avatarHtml, $topbarHtml);
            ?>

            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Filtros -->
                <!-- Barra de Pesquisa com Filtros -->
                <div class="mb-8">
                    <div class="max-w-4xl mx-auto">
                        <form method="GET" action="" class="relative">
                            <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-4 md:space-y-0">
                                <!-- Search Input Group -->
                                <div class="flex-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-gray-400">search</span>
                                    </div>
                                    <input type="text" name="search" placeholder="Pesquisar formulários..."
                                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm text-gray-600 placeholder-gray-400 transition-all duration-200"
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                </div>

                                <!-- Select Filter Group -->
                                <div class="relative w-full md:w-64">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-gray-400">filter_list</span>
                                    </div>
                                    <select name="tipo"
                                        class="block w-full pl-10 pr-10 py-2.5 border border-gray-200 rounded-lg appearance-none bg-white shadow-sm text-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer transition-all duration-200">
                                        <option value="">Todos os tipos</option>

                                        <!-- Grupo SAC -->
                                        <optgroup label="SAC">
                                            <option value="SAC_GRUPO"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'SAC_GRUPO') ? 'selected' : ''; ?>>
                                                Todos SAC</option>
                                            <option value="SAC"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'SAC') ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;SAC Individual</option>
                                        </optgroup>

                                        <!-- Grupo Recursos e Defesas -->
                                        <optgroup label="Recursos e Defesas">
                                            <option value="JARI_GRUPO"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'JARI_GRUPO') ? 'selected' : ''; ?>>
                                                Todos os Recursos</option>
                                            <option value="JARI_apresentacao_condutor"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'JARI_apresentacao_condutor') ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;Apresentação de Condutor</option>
                                            <option value="JARI_defesa_previa"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'JARI_defesa_previa') ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;Defesa Prévia</option>
                                            <option value="JARI_jari"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'JARI_jari') ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;Recurso JARI</option>
                                        </optgroup>

                                        <!-- Grupo Credenciais -->
                                        <optgroup label="Credenciais">
                                            <option value="CREDENCIAIS_GRUPO"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'CREDENCIAIS_GRUPO') ? 'selected' : ''; ?>>
                                                Todas as Credenciais</option>
                                            <option value="PCD"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'PCD') ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;PCD</option>
                                            <option value="IDOSO"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'IDOSO') ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;IDOSO</option>
                                        </optgroup>

                                        <!-- Grupo Outros -->
                                        <optgroup label="Outros">
                                            <option value="OUTROS_GRUPO"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'OUTROS_GRUPO') ? 'selected' : ''; ?>>
                                                Todos os Outros</option>
                                            <option value="DAT"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'DAT') ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;DAT</option>
                                            <option value="Parecer"
                                                <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'Parecer') ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;Parecer</option>
                                        </optgroup>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <span class="material-icons text-gray-400">expand_more</span>
                                    </div>
                                </div>

                                <!-- Search Button -->
                                <button type="submit"
                                    class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <span class="material-icons mr-2">search</span>
                                    Buscar
                                </button>
                            </div>

                            <!-- Active Filters (if any) -->
                            <?php if (!empty($_GET['search']) || !empty($_GET['tipo'])): ?>
                                <div class="mt-4 flex items-center space-x-2">
                                    <span class="text-sm text-gray-500">Filtros ativos:</span>
                                    <?php if (!empty($_GET['search'])): ?>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                            "<?php echo htmlspecialchars($_GET['search']); ?>"
                                            <a href="?<?php echo http_build_query(array_merge($_GET, ['search' => ''])); ?>"
                                                class="ml-2 text-blue-600 hover:text-blue-800">
                                                <span class="material-icons text-sm">close</span>
                                            </a>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($_GET['tipo'])): ?>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800">
                                            Tipo: <?php echo htmlspecialchars($_GET['tipo']); ?>
                                            <a href="?<?php echo http_build_query(array_merge($_GET, ['tipo' => ''])); ?>"
                                                class="ml-2 text-gray-600 hover:text-gray-800">
                                                <span class="material-icons text-sm">close</span>
                                            </a>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="flex justify-end mb-4 space-x-4">
                    <!-- Toggle Não Lidos -->
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                        <button @click="showUnreadOnly = !showUnreadOnly" type="button"
                            :class="{ 'bg-blue-100 text-blue-600': showUnreadOnly, 'hover:bg-gray-50': !showUnreadOnly }"
                            class="inline-flex items-center px-3 py-1.5 rounded-md transition-all duration-200">
                            <span class="material-icons text-lg mr-1">mark_email_unread</span>
                            Não Lidos
                            <?php
                            // Contar total de não lidos
                            $total_nao_lidos = array_reduce($submissoes, function ($carry, $item) {
                                return $carry + (!isset($item['is_read']) || $item['is_read'] == 0 ? 1 : 0);
                            }, 0);
                            ?>
                            <span class="ml-2 bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full">
                                <?php echo $total_nao_lidos; ?>
                            </span>
                            <?php if ($apenas_nao_lidos): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['nao_lidos' => null])); ?>"
                                    class="ml-2 text-blue-600 hover:text-blue-800">
                                    <span class="material-icons text-sm">close</span>
                                </a>
                            <?php endif; ?>
                        </button>
                    </div>

                    <!-- Toggle Pendentes -->
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pendentes' => $apenas_pendentes ? null : 'true', 'pagina' => 1])); ?>"
                            class="inline-flex items-center px-3 py-1.5 rounded-md transition-all duration-200 <?php echo $apenas_pendentes ? 'bg-yellow-100 text-yellow-600' : 'hover:bg-gray-50'; ?>">
                            <span class="material-icons text-lg mr-1">pending_actions</span>
                            Pendentes
                            <?php
                            // Contar total de pendentes
                            $total_pendentes = array_reduce($submissoes, function ($carry, $item) {
                                return $carry + ((!isset($item['situacao']) || $item['situacao'] !== 'Concluído') ? 1 : 0);
                            }, 0);
                            ?>
                            <span class="ml-2 bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">
                                <?php echo $total_pendentes; ?>
                            </span>
                            <?php if ($apenas_pendentes): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pendentes' => null])); ?>"
                                    class="ml-2 text-yellow-600 hover:text-yellow-800">
                                    <span class="material-icons text-sm">close</span>
                                </a>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Toggle Pareceres Próximos -->
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1"
                        x-data="{ showDetails: false }">
                        <div class="relative">
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['pareceres_proximos' => $apenas_pareceres_proximos ? null : 'true', 'pagina' => 1])); ?>"
                                @mouseenter="showDetails = true" @mouseleave="showDetails = false"
                                class="inline-flex items-center px-3 py-1.5 rounded-md transition-all duration-200 <?php echo $apenas_pareceres_proximos ? 'bg-purple-100 text-purple-600' : 'hover:bg-gray-50'; ?>">
                                <span class="material-icons text-lg mr-1">event</span>
                                Pareceres Próximos
                                <?php
                                // Array para armazenar detalhes dos pareceres próximos
                                $pareceres_proximos_detalhes = array();

                                // Coletar detalhes dos pareceres próximos
                                foreach ($submissoes as $item) {
                                    if (
                                        $item['tipo'] === 'Parecer' &&
                                        isset($item['data_horario']) &&
                                        (!isset($item['situacao']) || $item['situacao'] !== 'Concluído')
                                    ) {
                                        $partes = explode(' ', $item['data_horario']);
                                        if (isset($partes[0])) {
                                            $data = DateTime::createFromFormat('d/m/Y', $partes[0]);
                                            if ($data) {
                                                $hoje = new DateTime();
                                                $hoje->setTime(0, 0, 0);
                                                $data->setTime(0, 0, 0);

                                                if ($data >= $hoje && $data <= (new DateTime('+7 days'))) {
                                                    $pareceres_proximos_detalhes[] = array(
                                                        'data' => $partes[0],
                                                        'horario' => $partes[1] ?? 'Não definido',
                                                        'local' => $item['local'] ?? 'Local não definido',
                                                        'evento' => $item['evento'] ?? 'Evento não especificado'
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                                ?>
                                <span class="ml-2 bg-purple-500 text-white text-xs px-2 py-0.5 rounded-full">
                                    <?php echo count($pareceres_proximos_detalhes); ?>
                                </span>

                                <!-- Tooltip/Popover com detalhes -->
                                <div x-show="showDetails" x-cloak
                                    class="absolute left-0 top-full mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                    @click.away="showDetails = false">
                                    <div class="p-4">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Próximos Pareceres</h4>
                                        <?php if (empty($pareceres_proximos_detalhes)): ?>
                                            <p class="text-sm text-gray-500">Nenhum parecer programado para os próximos 7
                                                dias.</p>
                                        <?php else: ?>
                                            <div class="space-y-3">
                                                <?php foreach ($pareceres_proximos_detalhes as $parecer): ?>
                                                    <div class="border-b border-gray-100 pb-2 last:border-0">
                                                        <div class="flex items-start">
                                                            <span
                                                                class="material-icons text-purple-500 mr-2 text-sm">event</span>
                                                            <div class="flex-1">
                                                                <p class="text-sm font-medium text-gray-700">
                                                                    <?php echo $parecer['data']; ?> às
                                                                    <?php echo $parecer['horario']; ?></p>
                                                                <p class="text-xs text-gray-600">
                                                                    <?php echo $parecer['local']; ?></p>
                                                                <p class="text-xs text-gray-500">
                                                                    <?php echo $parecer['evento']; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                            <?php if ($apenas_pareceres_proximos): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pareceres_proximos' => null])); ?>"
                                    class="ml-2 text-purple-600 hover:text-purple-800">
                                    <span class="material-icons text-sm">close</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Visualização Grade/Lista -->
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1">
                        <button @click="viewMode = 'grid'" type="button"
                            :class="{ 'bg-blue-100 text-blue-600': viewMode === 'grid', 'hover:bg-gray-50': viewMode !== 'grid' }"
                            class="inline-flex items-center px-3 py-1.5 rounded-md transition-all duration-200">
                            <span class="material-icons text-lg mr-1">grid_view</span>
                            Grade
                        </button>
                        <button @click="viewMode = 'list'" type="button"
                            :class="{ 'bg-blue-100 text-blue-600': viewMode === 'list', 'hover:bg-gray-50': viewMode !== 'list' }"
                            class="inline-flex items-center px-3 py-1.5 rounded-md transition-all duration-200">
                            <span class="material-icons text-lg mr-1">view_list</span>
                            Lista
                        </button>
                    </div>
                </div>

                <!-- Cards Container -->
                <div x-bind:class="{
                    'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4': viewMode === 'grid',
                    'space-y-3': viewMode === 'list'
                }">
                    <?php
                    // Filtrar DATs incompletos (sem token DAT4)
                    $submissoes_filtradas = array_filter($submissoes_pagina, function ($item) {
                        // Se for DAT, verificar se está completo (tem token DAT4)
                        if ($item['tipo'] === 'DAT') {
                            return isset($item['token_dat4']) && !empty($item['token_dat4']);
                        }
                        // Para outros tipos de formulários, sempre mostrar
                        return true;
                    });

                    if (empty($submissoes_filtradas)):
                    ?>
                        <div class="bg-white rounded-lg shadow-sm p-6 text-center flex flex-col items-center">
                            <span class="material-icons text-gray-400 mb-4" style="font-size: 48px;">inbox</span>
                            <p class="text-gray-600 text-lg">
                                <?php echo $apenas_nao_lidos ? 'Nenhum formulário não lido encontrado.' : 'Nenhum formulário encontrado.'; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($submissoes_filtradas as $item): ?>
                            <?php $style = getFormularioStyle($item['tipo'], $item['subtipo'] ?? null); ?>

                            <div class="form-card bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 <?php echo ($item['situacao'] ?? '') === 'Concluído' ? 'card-completed' : ''; ?>"
                                :class="{
                                    'border-l-4': viewMode === 'grid',
                                    [<?php echo "'$style[border]'"; ?>]: viewMode === 'grid',
                                    'border border-gray-200': viewMode === 'list',
                                    ['hover:border-<?php echo explode('-', $style['border'])[1]; ?>']: viewMode === 'list'
                                }">
                                <!-- Grid View -->
                                <template x-if="viewMode === 'grid'">
                                    <div class="p-5">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center">
                                                <!-- Adicionar indicador de não lido -->
                                                <div class="relative">
                                                    <span
                                                        class="material-icons <?php echo $style['text']; ?> mr-2"><?php echo $style['icon']; ?></span>
                                                    <?php if (!isset($item['is_read']) || $item['is_read'] == 0): ?>
                                                        <span
                                                            class="top-0 right-0 absolute w-3 h-3 bg-blue-500 border-2 border-white rounded-full"></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <?php echo renderItemHeader($item); ?>
                                                </div>
                                            </div>
                                            <span class="text-sm text-gray-500">#<?php echo $item['id']; ?></span>
                                        </div>

                                        <!-- Substituir o conteúdo do card por uma única chamada de função -->
                                        <div class="space-y-2">
                                            <?php echo renderInfoLine('Solicitante', $item['nome']); ?>
                                            <?php echo renderInfoLine('Data', date('d/m/Y H:i', strtotime($item['data_submissao'])) . ' <span class="text-gray-500 text-xs ml-1">(' . tempoRelativo($item['data_submissao']) . ')</span>', true); ?>
                                            <?php echo renderizarInfoCard($item); ?>
                                        </div>

                                        <div class="mt-4 pt-2">
                                            <a href="detalhes_formulario.php?id=<?php echo $item['id']; ?>&tipo=<?php
                                                                                                                $tipoRedirect = $item['tipo'];
                                                                                                                if ($tipoRedirect === 'IDOSO') {
                                                                                                                    $tipoRedirect = 'PCD';
                                                                                                                }
                                                                                                                echo $tipoRedirect;
                                                                                                                ?>&pagina_anterior=<?php echo $pagina; ?>&search_anterior=<?php echo urlencode($search); ?>&tipo_anterior=<?php echo urlencode($tipo_filter); ?>&view_anterior=<?php echo urlencode($view_mode); ?>"
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                <span class="material-icons text-sm mr-2">visibility</span>
                                                Visualizar detalhes
                                            </a>
                                        </div>
                                    </div>
                                </template>

                                <!-- List View -->
                                <template x-if="viewMode === 'list'">
                                    <div class="flex items-center p-4">
                                        <div class="flex items-center justify-between w-full">
                                            <!-- Left section: Icon and Basic Info -->
                                            <div class="flex items-center space-x-4">
                                                <!-- Adicionar indicador de não lido -->
                                                <div class="relative">
                                                    <div class="<?php echo $style['bg']; ?> p-2 rounded-lg">
                                                        <span
                                                            class="material-icons <?php echo $style['text']; ?>"><?php echo $style['icon']; ?></span>
                                                    </div>
                                                    <?php if (!isset($item['is_read']) || $item['is_read'] == 0): ?>
                                                        <span
                                                            class="top-0 right-0 absolute w-3 h-3 bg-blue-500 border-2 border-white rounded-full"></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="flex items-center space-x-2">
                                                        <h3 class="font-semibold text-gray-800 flex items-center">
                                                            #<?php echo str_pad($item['id'], 6, '0', STR_PAD_LEFT); ?> -
                                                            <?php echo safeString($item['nome']); ?>
                                                            <span
                                                                class="px-2 py-1 text-xs rounded-full <?php echo $style['bg']; ?> <?php echo $style['text']; ?> ml-2">
                                                                <?php if ($item['tipo'] === 'JARI' && isset($item['subtipo'])): ?>
                                                                    <?php echo getTipoJariLabel($item['subtipo'])['titulo']; ?>
                                                                <?php else: ?>
                                                                    <?php echo $item['tipo']; ?>
                                                                <?php endif; ?>
                                                            </span>
                                                            <?php if ($item['situacao'] === 'Concluído'): ?>
                                                                <span
                                                                    class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full ml-2">Concluído</span>
                                                            <?php endif; ?>
                                                            <?php if (!isset($item['is_read']) || $item['is_read'] == 0): ?>
                                                                <span
                                                                    class="text-xs bg-blue-500 text-white px-2 py-1 rounded-full ml-2">Novo</span>
                                                            <?php endif; ?>
                                                        </h3>
                                                        <p class="text-sm text-gray-500">
                                                            <?php if ($item['tipo'] === 'SAC'): ?>
                                                                Assunto:
                                                                <?php echo htmlspecialchars($item['assunto'] ?? 'Não informado'); ?>
                                                                •
                                                                Departamento:
                                                                <?php echo htmlspecialchars($item['departamento'] ?? 'Não informado'); ?>
                                                            <?php elseif ($item['tipo'] === 'JARI'): ?>
                                                                Auto: <a
                                                                    href="detalhes_formulario.php?id=<?php echo $item['id']; ?>&tipo=JARI"
                                                                    class="text-blue-600 hover:text-blue-800">
                                                                    <?php echo htmlspecialchars($item['autoInfracao'] ?? 'Não informado'); ?>
                                                                </a> •
                                                                Placa:
                                                                <?php echo htmlspecialchars($item['placa'] ?? 'Não informado'); ?> •
                                                                Protocolo:
                                                                #<?php echo str_pad($item['id'], 6, '0', STR_PAD_LEFT); ?> •
                                                                <span x-data="{ expanded: false }">
                                                                    Defesa:
                                                                    <span :class="{ 'line-clamp-1': !expanded }" class="inline">
                                                                        <?php echo htmlspecialchars($item['defesa'] ?? 'Não informada'); ?>
                                                                    </span>
                                                                    <button @click="expanded = !expanded"
                                                                        class="text-xs text-blue-600 hover:text-blue-800 inline-flex items-center ml-1">
                                                                        <span x-text="expanded ? 'menos' : 'mais'"></span>
                                                                        <span class="material-icons text-sm"
                                                                            x-text="expanded ? 'expand_less' : 'expand_more'"></span>
                                                                    </button>
                                                                </span>
                                                            <?php elseif ($item['tipo'] === 'PCD' || $item['tipo'] === 'IDOSO'): ?>
                                                                CPF:
                                                                <?php echo htmlspecialchars($item['cpf'] ?? 'Não informado'); ?> •
                                                                Cartão:
                                                                <?php echo isset($item['n_cartao']) ? htmlspecialchars($item['n_cartao']) : 'Pendente'; ?>
                                                            <?php elseif ($item['tipo'] === 'DAT'): ?>
                                                                Local:
                                                                <?php echo htmlspecialchars($item['local_acidente'] ?? 'Não informado'); ?>
                                                                •
                                                                Data do Acidente:
                                                                <?php echo isset($item['data_acidente']) ? date('d/m/Y', strtotime($item['data_acidente'])) : 'Não informado'; ?>
                                                            <?php endif; ?>
                                                            •
                                                            <?php echo date('d/m/Y H:i', strtotime($item['data_submissao'])); ?>
                                                            <span
                                                                class="text-gray-500 text-xs ml-1">(<?php echo tempoRelativo($item['data_submissao']); ?>)</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Right section: Status and Action -->
                                            <div class="flex items-center space-x-4">
                                                <?php if ($item['tipo'] === 'Parecer'): ?>
                                                    <?php echo renderizarInfoParecer($item); ?>
                                                <?php endif; ?>

                                                <?php if ($item['tipo'] === 'DAT'): ?>
                                                    <div class="hidden md:flex items-center space-x-4 text-sm">
                                                        <span class="text-gray-500">
                                                            <?php echo htmlspecialchars($item['email_usuario'] ?? $item['email'] ?? 'Não informado'); ?>
                                                        </span>
                                                        <span class="px-2 py-1 rounded-full text-xs <?php
                                                                                                    $preenchimento = $item['preenchimento_status'] ?? 'Incompleto';
                                                                                                    echo $preenchimento === 'Incompleto' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
                                                                                                    ?>">
                                                            <?php echo htmlspecialchars($preenchimento); ?>
                                                        </span>
                                                        <span class="px-2 py-1 rounded-full text-xs <?php
                                                                                                    $status = $item['status'] ?? 'Pendente';
                                                                                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                                                                                    if ($status === 'Concluído') {
                                                                                                        $statusClass = 'bg-green-100 text-green-800';
                                                                                                    } elseif ($status === 'Em análise') {
                                                                                                        $statusClass = 'bg-blue-100 text-blue-800';
                                                                                                    } elseif ($status === 'Recusado') {
                                                                                                        $statusClass = 'bg-red-100 text-red-800';
                                                                                                    }
                                                                                                    echo $statusClass;
                                                                                                    ?>">
                                                            <?php echo htmlspecialchars($status); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>

                                                <a href="detalhes_formulario.php?id=<?php echo $item['id']; ?>&tipo=<?php
                                                                                                                    $tipoRedirect = $item['tipo'];
                                                                                                                    if ($tipoRedirect === 'IDOSO') {
                                                                                                                        $tipoRedirect = 'PCD';
                                                                                                                    }
                                                                                                                    echo $tipoRedirect;
                                                                                                                    ?>&pagina_anterior=<?php echo $pagina; ?>&search_anterior=<?php echo urlencode($search); ?>&tipo_anterior=<?php echo urlencode($tipo_filter); ?>&view_anterior=<?php echo urlencode($view_mode); ?>"
                                                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                    <span class="material-icons text-sm mr-2">visibility</span>
                                                    Detalhes
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>


                <!-- Paginação -->
                <div class="flex justify-center mt-6 space-x-2">
                    <?php if ($pagina > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Anterior</a>
                    <?php endif; ?>
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded">Página <?php echo $pagina; ?> de
                        <?php echo $total_pages; ?></span>
                    <?php if ($pagina < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Próxima</a>
                    <?php endif; ?>
                </div>

            </main>

            <!-- Footer -->
            <footer class="bg-white shadow-md py-4 px-6">
                <p class="text-gray-600 text-center">&copy; <?php echo date('Y'); ?> Departamento de Trânsito. Todos os
                    direitos reservados.</p>
            </footer>
        </div>
    </div>
</body>

</html>