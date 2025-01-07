<?php
function contarNotificacoesNaoLidas($conn)
{
    $tresDiasAtras = date('Y-m-d H:i:s', strtotime('-3 days'));

    $sql = "SELECT (
        (SELECT COUNT(*) FROM formularios_dat_central WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL)) +
        (SELECT COUNT(*) FROM Parecer WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL)) +
        (SELECT COUNT(*) FROM sac WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL)) +
        (SELECT COUNT(*) FROM solicitacao_cartao WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL)) +
        (SELECT COUNT(*) FROM solicitacoes_demutran WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL))
    ) as total";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $tresDiasAtras, $tresDiasAtras, $tresDiasAtras, $tresDiasAtras, $tresDiasAtras);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

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
        'index' => ['icon' => 'dashboard', 'text' => 'Dashboard'],
        'formularios' => ['icon' => 'description', 'text' => 'Formulários'],
        'gerenciar_noticias' => ['icon' => 'newspaper', 'text' => 'Notícias'],
        'analytics' => ['icon' => 'query_stats', 'text' => 'Estatísticas'],
        'usuarios' => ['icon' => 'group', 'text' => 'Usuários'],
        'perfil' => ['icon' => 'account_circle', 'text' => 'Perfil']
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


function getNotificacoesNaoLidas($conn, $usuario_id) {
    // Obter data de 3 dias atrás
    $tresDiasAtras = date('Y-m-d H:i:s', strtotime('-3 days'));
    
    $sql = "SELECT * FROM (
        SELECT 
            'DAT' as tipo,
            id,
            token as titulo,
            status as situacao,
            data_submissao as data_submissao,
            NULL as nome,
            NULL as email,
            NULL as cidade,
            NULL as subtipo
        FROM formularios_dat_central
        WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL)
        
        UNION ALL
        
        SELECT 
            'Parecer' as tipo,
            id,
            protocolo as titulo,
            situacao,
            data_submissao,
            nome,
            email,
            local as cidade,
            evento as subtipo
        FROM Parecer
        WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL)
        
        UNION ALL
        
        SELECT 
            'SAC' as tipo,
            id,
            assunto as titulo,
            situacao,
            data_submissao,
            nome,
            email,
            NULL as cidade,
            tipo_contato as subtipo
        FROM sac
        WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL)
        
        UNION ALL
        
        SELECT 
            'Cartao' as tipo,
            id,
            CONCAT(tipo_solicitacao, ' - ', n_cartao) as titulo,
            situacao,
            data_submissao,
            nome,
            email,
            NULL as cidade,
            tipo_solicitacao as subtipo
        FROM solicitacao_cartao
        WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL)
        
        UNION ALL
        
        SELECT 
            'Demutran' as tipo,
            id,
            tipo_solicitacao as titulo,
            situacao,
            data_submissao,
            nome,
            email,
            municipio as cidade,
            tipo_solicitacao as subtipo
        FROM solicitacoes_demutran
        WHERE data_submissao >= ? AND (is_read = 0 OR is_read IS NULL)
        
    ) AS combined_results 
    ORDER BY data_submissao DESC 
    LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $tresDiasAtras, $tresDiasAtras, $tresDiasAtras, $tresDiasAtras, $tresDiasAtras);
    $stmt->execute();
    return $stmt->get_result();
}

function marcarComoLido($conn, $tipo, $registro_id) {
    $tabela = match($tipo) {
        'DAT' => 'formularios_dat_central',
        'Parecer' => 'Parecer',
        'SAC' => 'sac',
        'Cartao' => 'solicitacao_cartao',
        'Demutran' => 'solicitacoes_demutran',
        default => null
    };

    if (!$tabela) return false;

    $sql = "UPDATE {$tabela} SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $registro_id);
    return $stmt->execute();
}

// ...existing code...

function getFormularioStyle($tipo, $subtipo = null)
{
    $styles = [
        'SAC' => [
            'icon' => 'support_agent',
            'text' => 'text-purple-600',
            'bg' => 'bg-purple-100',
            'border' => 'border-purple-500'
        ],
        'JARI' => [
            'icon' => 'gavel', // ícone padrão para JARI
            'text' => 'text-orange-600',
            'bg' => 'bg-orange-100',
            'border' => 'border-orange-500'
        ],
        'PCD' => [
            'icon' => 'accessible',
            'text' => 'text-blue-600',
            'bg' => 'bg-blue-100',
            'border' => 'border-blue-500'
        ],
        'IDOSO' => [
            'icon' => 'elderly',
            'text' => 'text-green-600',
            'bg' => 'bg-green-100',
            'border' => 'border-green-500'
        ],
        'DAT' => [
            'icon' => 'car_crash', // Atualizado para um ícone mais apropriado
            'text' => 'text-red-600',
            'bg' => 'bg-red-100',
            'border' => 'border-red-500'
        ],
        'Parecer' => [
            'icon' => 'assignment',
            'text' => 'text-gray-600',
            'bg' => 'bg-gray-100',
            'border' => 'border-gray-500'
        ]
    ];

    // Ícones específicos para subtipos JARI
    if ($tipo === 'JARI' && $subtipo) {
        switch ($subtipo) {
            case 'apresentacao_condutor':
                $styles['JARI']['icon'] = 'person_add';
                break;
            case 'defesa_previa':
                $styles['JARI']['icon'] = 'shield';
                break;
            case 'jari':
                $styles['JARI']['icon'] = 'balance';
                break;
        }
    }

    return $styles[$tipo] ?? $styles['SAC'];
}


function getTopbarHtml($pageTitle, $usuario_id) {
    global $conn;
    
    $notificacoes = getNotificacoesNaoLidas($conn, $usuario_id);
    $notificacoesNaoLidas = $notificacoes->num_rows;
    
    $notificacoesHtml = '';
    while ($notif = $notificacoes->fetch_assoc()) {
        $style = getFormularioStyle($notif['tipo'], $notif['subtipo'] ?? null);
        $data = date('d/m H:i', strtotime($notif['data_submissao'])); // Formato mais curto de data
        $titulo = htmlspecialchars((string)($notif['titulo'] ?? ''));
        $nome = htmlspecialchars((string)($notif['nome'] ?? 'Não informado'));
        
        // Mapear tipo para redirecionamento
        $tipoRedirect = match($notif['tipo']) {
            'DAT4' => 'DAT',
            'Cartao' => 'PCD',
            'Demutran' => 'JARI',
            default => $notif['tipo']
        };

        $link = "detalhes_formulario.php?id={$notif['id']}&tipo={$tipoRedirect}";

        $notificacoesHtml .= "
        <li class='p-3 border-b hover:bg-gray-50'> <!-- Reduzido padding -->
            <a href='{$link}' class='block' onclick='marcarComoLido(\"{$notif['tipo']}\", {$notif['id']})'>
                <div class='flex items-center gap-3'>
                    <div class='{$style['bg']} p-2 rounded'>
                        <span class='material-icons {$style['text']} text-lg'>{$style['icon']}</span>
                    </div>
                    <div class='flex-1 min-w-0'> <!-- min-w-0 para permitir truncamento -->
                        <div class='flex items-center gap-2 mb-0.5'>
                            <p class='font-medium text-gray-900 truncate'>{$nome}</p>
                            <span class='px-1.5 py-0.5 text-xs rounded {$style['bg']} {$style['text']} whitespace-nowrap'>
                                {$notif['tipo']}
                            </span>
                        </div>
                        <p class='text-sm text-gray-600 truncate'>{$titulo}</p>
                        <p class='text-xs text-gray-400 mt-0.5'>{$data}</p>
                    </div>
                </div>
            </a>
        </li>";
    }

    if ($notificacoesHtml === '') {
        $notificacoesHtml = "
        <li class='p-4 text-center text-gray-500'>
            Nenhuma notificação nova
        </li>";
    }

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
                <div x-show='open' 
                     @click.away='open = false' 
                     x-cloak
                     class='absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg z-50'
                     style='display: none;'>
                    <div class='p-4 border-b'>
                        <div class='flex items-center justify-between'>
                            <span class='font-bold text-gray-700'>Notificações</span>
                            <span class='text-xs text-blue-500 hover:text-blue-700'>
                                <a href='formularios.php?tipo=SAC'>Ver todas</a>
                            </span>
                        </div>
                    </div>
                    <ul class='max-h-96 overflow-y-auto divide-y divide-gray-100'>
                        {$notificacoesHtml}
                    </ul>
                </div>
            </div>
            <div x-data='{ open: false }' class='relative'>
                <button @click='open = !open' class='flex items-center focus:outline-none'>
                    [AVATAR_PLACEHOLDER]
                </button>
                <div x-show='open' 
                     @click.away='open = false' 
                     x-cloak 
                     class='absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-50'>
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