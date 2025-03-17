<?php

declare(strict_types=1);

// Funções para Parecer
function formatarDataParecer(?string $data_horario): ?DateTime
{
    if (!$data_horario) return null;

    $partes = explode(' ', $data_horario);
    if (!isset($partes[0])) return null;

    $data = DateTime::createFromFormat('d/m/Y', $partes[0]);
    if (!$data) return null;

    $data->setTime(0, 0, 0);
    return $data;
}

function isParecerProximo(array $parecer): bool
{
    $data = formatarDataParecer($parecer['data_horario'] ?? null);
    if (!$data) return false;

    $hoje = new DateTime();
    $hoje->setTime(0, 0, 0);

    return $data >= $hoje && $data <= (new DateTime('+7 days'));
}

function contarPareceresPróximos(array $submissoes): int
{
    return array_reduce($submissoes, function ($carry, $item) {
        if ($item['tipo'] === 'Parecer' && isParecerProximo($item)) {
            return $carry + 1;
        }
        return $carry;
    }, 0);
}

function filtrarParecerProximo(array $item): bool
{
    if ($item['tipo'] !== 'Parecer') return false;
    return isParecerProximo($item);
}

function renderizarInfoParecer(array $item): string
{
    $html = '';

    if ($item['tipo'] === 'Parecer') {
        $html .= renderInfoLine('Local', $item['local'] ?? null);
        $html .= renderInfoLine('Evento', $item['evento'] ?? null);
        $html .= renderInfoLine('Data/Horário', $item['data_horario'] ?? null);
        $html .= renderInfoLine('Protocolo', $item['protocolo'] ?? null);
    }

    return $html;
}

// Funções para SAC
function renderizarInfoSac(array $item): string
{
    $html = '';
    $html .= renderInfoLine('Assunto', $item['assunto'] ?? null);
    $html .= renderInfoLine('Departamento', $item['departamento'] ?? null);
    $html .= renderInfoLine('Protocolo', str_pad($item['id'], 6, '0', STR_PAD_LEFT));
    $html .= renderInfoLine('Data', date('d/m/Y H:i', strtotime($item['data_submissao'])));
    return $html;
}

// Funções para JARI
function renderizarInfoJari(array $item): string
{
    $html = '';
    $html .= renderInfoLine('Auto de Infração', $item['autoInfracao'] ?? null);
    $html .= renderInfoLine('Placa do Veículo', $item['placa'] ?? null);
    $html .= renderInfoLine('Protocolo', str_pad($item['id'], 6, '0', STR_PAD_LEFT));
    $html .= renderInfoLine('Data', date('d/m/Y H:i', strtotime($item['data_submissao'])));

    // Adiciona seção expansível para defesa
    $html .= "<div x-data=\"{ expanded: false }\" class=\"flex flex-col border-b border-gray-100 pb-2\">
        <div class=\"flex justify-between items-center mb-1\">
            <span class=\"text-sm text-gray-600\">Defesa</span>
            <button @click=\"expanded = !expanded\" 
                    class=\"text-xs text-blue-600 hover:text-blue-800 flex items-center\">
                <span x-text=\"expanded ? 'Mostrar menos' : 'Ler mais'\"></span>
                <span class=\"material-icons text-sm ml-1\" 
                      x-text=\"expanded ? 'expand_less' : 'expand_more'\"></span>
            </button>
        </div>
        <p class=\"text-sm text-gray-800 transition-all duration-200\"
           :class=\"{ 'line-clamp-2': !expanded }\">
            " . htmlspecialchars($item['defesa'] ?? 'Não informada') . "
        </p>
    </div>";

    return $html;
}

// Funções para PCD/IDOSO
function renderizarInfoCredencial(array $item): string
{
    $html = '';
    $html .= renderInfoLine('CPF', $item['cpf'] ?? null);
    $html .= renderInfoLine(
        'Status do Cartão',
        isset($item['n_cartao']) ? 'Emitido: ' . htmlspecialchars($item['n_cartao']) : 'Pendente de Emissão'
    );
    $html .= renderInfoLine(
        'Validade',
        isset($item['data_validade']) ? date('d/m/Y', strtotime($item['data_validade'])) : 'A definir'
    );
    return $html;
}

// Funções para DAT
function renderizarInfoDat(array $item): string
{
    $html = '';
    $html .= renderInfoLine('Local do Acidente', $item['local_acidente'] ?? null);
    $html .= renderInfoLine(
        'Data do Acidente',
        isset($item['data_acidente']) ? date('d/m/Y', strtotime($item['data_acidente'])) : null
    );
    $html .= renderInfoLine(
        'Status',
        $item['preenchimento_status'] === 'Completo' ? 'Completo' : 'Incompleto'
    );
    return $html;
}

// Função geral para renderizar informações do card
function renderizarInfoCard(array $item): string
{
    $html = '';

    // Verifique o tipo de formulário e renderize informações específicas
    switch ($item['tipo']) {
        case 'SAC':
            $html .= renderInfoLine('Assunto', $item['assunto'] ?? 'Não informado');
            $html .= renderInfoLine('Departamento', $item['departamento'] ?? 'Não informado');
            break;

        case 'JARI':
            $html .= renderInfoLine('Auto de Infração', $item['autoInfracao'] ?? 'Não informado');
            $html .= renderInfoLine('Placa', $item['placa'] ?? 'Não informado');
            $html .= renderInfoLine('Defesa', substr($item['defesa'] ?? 'Não informada', 0, 100) . '...');
            break;

        case 'PCD':
        case 'IDOSO':
            $html .= renderInfoLine('CPF', $item['cpf'] ?? 'Não informado');
            $html .= renderInfoLine('Cartão', $item['n_cartao'] ?? 'Pendente');
            break;

        case 'DAT':
            $html .= renderInfoLine('E-mail', $item['email_usuario'] ?? $item['email'] ?? 'Não informado');
            $html .= renderInfoLine(
                'Status',
                '<span class="px-2 py-1 rounded-full text-xs ' .
                    ($item['status'] === 'Pendente' ? 'bg-yellow-100 text-yellow-800' : ($item['status'] === 'Concluído' ? 'bg-green-100 text-green-800' : ($item['status'] === 'Em análise' ? 'bg-blue-100 text-blue-800' : ($item['status'] === 'Recusado' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')))) .
                    '">' . htmlspecialchars($item['status'] ?? 'Pendente') . '</span>',
                true
            );
            $html .= renderInfoLine(
                'Preenchimento',
                '<span class="px-2 py-1 rounded-full text-xs ' .
                    ($item['preenchimento_status'] === 'Incompleto' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') .
                    '">' . htmlspecialchars($item['preenchimento_status'] ?? 'Incompleto') . '</span>',
                true
            );
            $html .= renderInfoLine('Token', '<span class="text-xs font-mono bg-gray-100 p-1 rounded">' .
                htmlspecialchars($item['token'] ?? 'Não disponível') . '</span>', true);
            break;

        case 'Parecer':
            $html .= renderizarInfoParecer($item);
            break;

        default:
            $html .= renderInfoLine('ID', '#' . $item['id']);
    }

    return $html;
}
