<?php

declare(strict_types=1);

session_start();
require_once '../env/config.php';
require_once './includes/template.php';
require_once './includes/formularios/cards.php';
require_once './includes/formularios/form_functions.php';

// Verificação de autenticação
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Inicialização de variáveis
$view_mode = setViewMode();
$notificacoesNaoLidas = contarNotificacoesNaoLidas($conn);

// Variáveis de paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 12;
$offset = ($pagina - 1) * $limite;

// Obter submissões paginadas de cada tabela
$sac = $conn->query("SELECT id, nome, email, assunto, data_submissao, situacao, is_read 
                     FROM sac 
                     ORDER BY id DESC 
                     LIMIT $limite OFFSET $offset");

$jari = $conn->query("SELECT id, nome, email, tipo_solicitacao as subtipo, 
                             data_submissao, situacao, is_read 
                      FROM solicitacoes_demutran 
                      ORDER BY id DESC 
                      LIMIT $limite OFFSET $offset");

$pcd = $conn->query("SELECT id, nome, email, tipo_solicitacao, data_submissao, 
                            situacao, is_read 
                     FROM solicitacao_cartao 
                     ORDER BY id DESC 
                     LIMIT $limite OFFSET $offset");

$dat = $conn->query("SELECT d4.id, d4.token, d1.nome, fc.email_usuario, 
                            fc.preenchimento_status, fc.data_submissao, 
                            fc.ultima_atualizacao 
                     FROM DAT4 d4 
                     LEFT JOIN DAT1 d1 ON d4.token = d1.token 
                     LEFT JOIN formularios_dat_central fc ON d4.token = fc.token 
                     ORDER BY d4.id DESC 
                     LIMIT $limite OFFSET $offset");

// Combinar todas as submissões em um array
$submissoes = [];

while ($row = $sac->fetch_assoc()) {
    $row['tipo'] = 'SAC';
    $submissoes[] = $row;
}

while ($row = $jari->fetch_assoc()) {
    $row['tipo'] = 'JARI';
    $submissoes[] = $row;
}

while ($row = $pcd->fetch_assoc()) {
    $row['tipo'] = strtoupper($row['tipo_solicitacao']);
    $submissoes[] = $row;
}

while ($row = $dat->fetch_assoc()) {
    $row['tipo'] = 'DAT';
    $row['email'] = $row['email_usuario'] ?? 'Não informado';
    $row['preenchimento_status'] = $row['preenchimento_status'] ?? 'Incompleto';
    $submissoes[] = $row;
}

// Obter parâmetros de pesquisa e filtro
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$tipo_filter = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$apenas_nao_lidos = isset($_GET['nao_lidos']) && $_GET['nao_lidos'] === 'true';
$apenas_pendentes = isset($_GET['pendentes']) && $_GET['pendentes'] === 'true';
$apenas_pareceres_proximos = isset($_GET['pareceres_proximos']) && $_GET['pareceres_proximos'] === 'true';

// Definir os tipos e tabelas correspondentes
$tipos = [
    'SAC' => 'sac',
    'JARI' => 'solicitacoes_demutran',
    'PCD' => 'solicitacao_cartao',
    'DAT' => 'formularios_dat_central',
    'Parecer' => 'Parecer'
];

// Inicializar array de submissões
$submissoes = [];

// Definir um limite maior para buscar mais registros para a filtragem
$fetch_limit = 100;

// Adicionar lógica para processar grupos no PHP (antes do foreach dos tipos)
$tipo_filter = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Expandir filtros de grupo para seus tipos correspondentes
if ($tipo_filter == 'SAC_GRUPO') {
    $tipos_selecionados = ['SAC'];
} elseif ($tipo_filter == 'JARI_GRUPO') {
    $tipos_selecionados = ['JARI'];
} elseif ($tipo_filter == 'CREDENCIAIS_GRUPO') {
    $tipos_selecionados = ['PCD', 'IDOSO'];
} elseif ($tipo_filter == 'OUTROS_GRUPO') {
    $tipos_selecionados = ['DAT', 'Parecer'];
} else {
    $tipos_selecionados = [$tipo_filter];
}

// Modificar a lógica de processamento das submissões
foreach ($tipos as $tipo => $tabela) {
    $is_jari_subtipo = strpos($tipo_filter, 'JARI_') === 0;
    $jari_subtipo = $is_jari_subtipo ? substr($tipo_filter, 5) : null;

    // Verificar se deve processar este tipo
    $should_process = empty($tipo_filter) || // Sem filtro
        in_array($tipo, $tipos_selecionados) || // Tipo específico ou grupo
        ($tipo == 'JARI' && $is_jari_subtipo); // Subtipo JARI

    if ($should_process) {
        if ($tabela === 'solicitacao_cartao') {
            // Tratamento para PCD e IDOSO
            $sql = "SELECT *, UPPER(tipo_solicitacao) as tipo FROM $tabela";
            if ($apenas_nao_lidos) {
                $sql .= " WHERE (is_read = 0 OR is_read IS NULL)";
            }
            $sql .= " ORDER BY id DESC LIMIT $fetch_limit";

            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $row['tipo'] = strtoupper($row['tipo_solicitacao']);
                if (
                    empty($tipo_filter) ||
                    in_array($row['tipo'], $tipos_selecionados) ||
                    $tipo_filter == 'CREDENCIAIS_GRUPO'
                ) {
                    $submissoes[] = $row;
                }
            }
        } elseif ($tabela === 'solicitacoes_demutran') {
            // Tratamento para JARI
            $sql = "SELECT *, tipo_solicitacao as subtipo FROM $tabela";
            $where_conditions = [];

            if ($is_jari_subtipo && $tipo_filter !== 'JARI_GRUPO') {
                $where_conditions[] = "tipo_solicitacao = '" . $conn->real_escape_string($jari_subtipo) . "'";
            }
            if ($apenas_nao_lidos) {
                $where_conditions[] = "(is_read = 0 OR is_read IS NULL)";
            }

            if (!empty($where_conditions)) {
                $sql .= " WHERE " . implode(' AND ', $where_conditions);
            }

            $sql .= " ORDER BY id DESC LIMIT $fetch_limit";

            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $row['tipo'] = 'JARI';
                $row['subtipo_label'] = ucfirst(str_replace('_', ' ', $row['subtipo']));
                $submissoes[] = $row;
            }
        } else {
            // Processamento para outras tabelas
            $sql = "SELECT * FROM $tabela";
            if ($apenas_nao_lidos) {
                $sql .= " WHERE (is_read = 0 OR is_read IS NULL)";
            }
            $sql .= " ORDER BY id DESC LIMIT $fetch_limit";

            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $row['tipo'] = $tipo;

                // Para 'DAT', buscar 'nome' na tabela 'DAT1'
                if ($tipo == 'DAT') {
                    $token = $conn->real_escape_string($row['token']);
                    $sql_nome = "SELECT nome FROM DAT1 WHERE token = '$token' LIMIT 1";
                    $result_nome = $conn->query($sql_nome);
                    if ($result_nome->num_rows > 0) {
                        $row_nome = $result_nome->fetch_assoc();
                        $row['nome'] = $row_nome['nome'];
                    } else {
                        $row['nome'] = 'Nome não encontrado';
                    }
                }

                $submissoes[] = $row;
            }
        }
    }
}

// Substituir a contagem antiga de pareceres próximos pela nova função
$total_pareceres_proximos = contarPareceresPróximos($submissoes);

// Aplicar filtro de pesquisa após coletar todas as submissões
if (!empty($search) || $apenas_pendentes || $apenas_pareceres_proximos) {
    $submissoes = array_filter($submissoes, function ($row) use ($search, $apenas_pendentes, $apenas_pareceres_proximos) {
        $match_search = empty($search) || (isset($row['nome']) && stripos($row['nome'], $search) !== false);
        $match_pendente = !$apenas_pendentes || (!isset($row['situacao']) || $row['situacao'] !== 'Concluído');
        $match_parecer_proximo = !$apenas_pareceres_proximos || filtrarParecerProximo($row);

        return $match_search && $match_pendente && $match_parecer_proximo;
    });
}

// Ordenar submissões por status (pendentes primeiro) e depois por data
usort($submissoes, function ($a, $b) {
    // Primeiro critério: status (pendentes primeiro)
    $statusA = ($a['situacao'] ?? '') === 'Concluído' ? 1 : 0;
    $statusB = ($b['situacao'] ?? '') === 'Concluído' ? 1 : 0;

    if ($statusA !== $statusB) {
        return $statusA - $statusB;
    }

    // Segundo critério: data (mais recentes primeiro)
    return strtotime($b['data_submissao']) - strtotime($a['data_submissao']);
});

// Paginação
$total_submissoes = count($submissoes);
$per_page = 10;
$total_pages = ceil($total_submissoes / $per_page);
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$start = ($pagina - 1) * $per_page;
$submissoes_pagina = array_slice($submissoes, $start, $per_page);