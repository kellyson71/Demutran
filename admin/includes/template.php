<?php
function getAvatarHtml($usuario_nome, $usuario_avatar = '') {
    $iniciais = strtoupper(mb_substr($usuario_nome, 0, 1) . mb_substr(strstr($usuario_nome, ' '), 1, 1));
    
    if ($usuario_avatar) {
        return "<img src='{$usuario_avatar}' alt='Avatar' 
                class='w-8 h-8 rounded-full object-cover ring-2 ring-blue-500 ring-offset-2'
                onerror=\"this.onerror=null; this.parentNode.innerHTML='<div class=\\\'w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold ring-2 ring-blue-500 ring-offset-2\\\'>{$iniciais}</div>';\">";
    } else {
        return "<div class='w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold ring-2 ring-blue-500 ring-offset-2'>
                {$iniciais}
              </div>";
    }
}

function getSidebarHtml($currentPage) {
    $menuItems = [
        'dashboard' => ['icon' => 'dashboard', 'text' => 'Dashboard'],
        'formularios' => ['icon' => 'assignment', 'text' => 'Formulários'],
        'gerenciar_noticias' => ['icon' => 'article', 'text' => 'Notícias'],
        'usuarios' => ['icon' => 'people', 'text' => 'Usuários'],
        'perfil' => ['icon' => 'person', 'text' => 'Perfil']
    ];

    $html = '<nav class="space-y-2 flex-1">';
    foreach ($menuItems as $page => $item) {
        $isActive = $currentPage === $page;
        $activeClass = $isActive ? 'bg-blue-50' : 'hover:bg-blue-50';
        $fontClass = $isActive ? 'font-semibold' : '';
        
        $html .= "
            <a href='{$page}.php' class='flex items-center p-2 text-gray-700 {$activeClass} rounded'>
                <span class='material-icons'>{$item['icon']}</span>
                <span class='ml-3 {$fontClass}'>{$item['text']}</span>
            </a>";
    }
    $html .= '</nav>';
    return $html;
}

function getTopbarHtml($pageTitle, $notificacoesNaoLidas) {
    return "
    <header class='bg-white shadow-md py-4 px-6 flex justify-between items-center'>
        <div class='flex items-center space-x-3'>
            <button @click='open = !open' class='md:hidden focus:outline-none'>
                <span class='material-icons'>menu</span>
            </button>
            <h2 class='text-xl font-semibold text-gray-800'>{$pageTitle}</h2>
        </div>
        <div class='flex items-center space-x-4'>
            <div x-data='{ open: false }' class='relative'>
                <button @click='open = !open' class='relative focus:outline-none'>
                    <span class='material-icons text-gray-700'>notifications</span>
                    " . ($notificacoesNaoLidas > 0 ? "<span class='absolute top-0 right-0 bg-red-600 text-white rounded-full px-1 text-xs'>{$notificacoesNaoLidas}</span>" : "") . "
                </button>
                <div x-show='open' @click.away='open = false' x-cloak class='absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50'>
                    <div class='p-4 border-b text-gray-700 font-bold'>Notificações</div>
                    <ul>
                        <li class='p-4 border-b hover:bg-gray-50'>
                            <a href='#' class='block'>
                                <p class='font-medium text-gray-800'>Nova mensagem</p>
                                <p class='text-sm text-gray-600'>Você tem uma nova mensagem.</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div x-data='{ open: false }' class='relative'>
                <button @click='open = !open' class='flex items-center focus:outline-none'>
                    [AVATAR_PLACEHOLDER]
                </button>
                <div x-show='open' @click.away='open = false' x-cloak class='absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-50'>
                    <div class='p-4 border-b text-gray-700 font-bold'>Perfil</div>
                    <ul>
                        <li class='p-4 hover:bg-gray-50'>
                            <a href='perfil.php' class='block text-gray-700'>Perfil</a>
                        </li>
                        <li class='p-4 hover:bg-gray-50'>
                            <a href='logout.php' class='block text-red-600'>Sair</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>";
}