<?php
function contarNotificacoesNaoLidas($conn) {
    // Obter data de 3 dias atrás
    $tresDiasAtras = date('Y-m-d H:i:s', strtotime('-3 days'));
    
    $sql = "SELECT 
        (SELECT COUNT(*) FROM formularios_dat_central WHERE data_criacao >= ? AND is_read = 0) +
        (SELECT COUNT(*) FROM Parecer WHERE data_submissao >= ? AND is_read = 0) +
        (SELECT COUNT(*) FROM sac WHERE data_submissao >= ? AND is_read = 0) +
        (SELECT COUNT(*) FROM solicitacao_cartao WHERE data_submissao >= ? AND is_read = 0) +
        (SELECT COUNT(*) FROM solicitacoes_demutran WHERE data_submissao >= ? AND is_read = 0) as total";
    
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
        'formularios' => ['icon' => 'assignment', 'text' => 'Formulários'],
        'gerenciar_noticias' => ['icon' => 'article', 'text' => 'Notícias'],
        'analytics' => ['icon' => 'analytics', 'text' => 'Estatísticas'],
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


function getNotificacoesNaoLidas($conn, $usuario_id) {
    // Obter data de 3 dias atrás
    $tresDiasAtras = date('Y-m-d H:i:s', strtotime('-3 days'));
    
    $sql = "SELECT * FROM (
        SELECT 
            'DAT' as tipo,
            d.id,
            d.token as titulo,
            d.status as situacao,
            d.data_criacao as data_submissao,
            dat1.nome as nome,
            dat1.email as email,
            dat1.cidade as cidade,
            dat1.tipo_acidente as subtipo,
            d.is_read
        FROM formularios_dat_central d
        LEFT JOIN DAT1 dat1 ON dat1.token = d.token
        WHERE d.data_criacao >= ? AND d.is_read = 0
        
        UNION ALL
        
        SELECT 
            'Parecer' as tipo,
            p.id,
            p.protocolo as titulo,
            p.situacao,
            p.data_submissao,
            p.nome,
            p.email,
            p.local as cidade,
            p.evento as subtipo,
            p.is_read
        FROM Parecer p
        WHERE p.data_submissao >= ? AND p.is_read = 0
        
        UNION ALL
        
        SELECT 
            'SAC' as tipo,
            s.id,
            s.assunto as titulo,
            s.situacao,
            s.data_submissao,
            s.nome,
            s.email,
            NULL as cidade,
            s.tipo_contato as subtipo,
            s.is_read
        FROM sac s
        WHERE s.data_submissao >= ? AND s.is_read = 0
        
        UNION ALL
        
        SELECT 
            'Cartao' as tipo,
            sc.id,
            CONCAT(sc.tipo_solicitacao, ' - ', sc.n_cartao) as titulo,
            sc.situacao,
            sc.data_submissao,
            sc.nome,
            sc.email,
            SUBSTRING_INDEX(sc.endereco, ',', -2) as cidade,
            sc.tipo_solicitacao as subtipo,
            sc.is_read
        FROM solicitacao_cartao sc
        WHERE sc.data_submissao >= ? AND sc.is_read = 0
        
        UNION ALL
        
        SELECT 
            'Demutran' as tipo,
            sd.id,
            sd.tipo_solicitacao as titulo,
            sd.situacao,
            sd.data_submissao,
            sd.nome,
            sd.email,
            sd.municipio as cidade,
            sd.tipo_solicitacao as subtipo,
            sd.is_read
        FROM solicitacoes_demutran sd
        WHERE sd.data_submissao >= ? AND sd.is_read = 0
        
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

function getFormularioStyle($tipo_formulario, $subtipo = null) {
    $styles = [
        'DAT' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'border' => 'border-yellow-500', 'icon' => 'description'],
        'SAC' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'border' => 'border-blue-500', 'icon' => 'support'],
        'JARI' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'border' => 'border-green-500', 'icon' => 'gavel'],
        'PCD' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'border' => 'border-purple-500', 'icon' => 'accessible'],
        'IDOSO' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'border' => 'border-orange-500', 'icon' => 'elderly'],
        'Cartao' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'border' => 'border-purple-500', 'icon' => 'credit_card'],
        'Parecer' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'border' => 'border-red-500', 'icon' => 'assignment']
    ];

    // Se for PCD ou IDOSO, verificar se veio do tipo_solicitacao
    if ($tipo_formulario === 'PCD' || $tipo_formulario === 'IDOSO') {
        return $styles[$tipo_formulario];
    }

    return $styles[$tipo_formulario] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'border' => 'border-gray-500', 'icon' => 'description'];
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
                     class='absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg z-50'>
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