<?php

declare(strict_types=1);

function setViewMode(): string
{
    if (isset($_GET['view'])) {
        $_SESSION['view_mode'] = $_GET['view'];
    }
    return $_SESSION['view_mode'] ?? 'grid';
}

function obterSubmissoesPaginadas(mysqli $conn, string $tabela, int $limite, int $offset): mysqli_result
{
    $sql = "SELECT * FROM $tabela ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $limite, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

function safeString(?string $value): string
{
    return htmlspecialchars($value ?? 'Não informado', ENT_QUOTES, 'UTF-8');
}

function getTipoJariLabel(string $subtipo): array
{
    return [
        'apresentacao_condutor' => ['titulo' => 'Apresentação de Condutor'],
        'defesa_previa' => ['titulo' => 'Defesa Prévia'],
        'jari' => ['titulo' => 'Recurso JARI']
    ][$subtipo] ?? ['titulo' => 'JARI'];
}

function renderStatusBadge(?string $situacao): string
{
    if (!$situacao) {
        return '';
    }

    if ($situacao === 'Concluído') {
        return '<span class="status-badge bg-green-100 text-green-800 ml-2">Concluído</span>';
    }
    return '';
}

function renderItemHeader(array $item): string
{
    $titulo = $item['tipo'] === 'JARI' && isset($item['subtipo'])
        ? getTipoJariLabel($item['subtipo'])['titulo']
        : $item['tipo'];

    return "
        <h3 class='text-lg font-semibold text-gray-800 flex items-center'>
            {$titulo}
            " . renderStatusBadge($item['situacao'] ?? null) . "
        </h3>
    ";
}

function renderInfoLine(string $label, ?string $value): string
{
    $value = htmlspecialchars($value ?? 'Não informado');
    return "
        <div class='flex justify-between items-center border-b border-gray-100 pb-2'>
            <span class='text-sm text-gray-600'>{$label}</span>
            <span class='text-sm font-medium text-gray-800'>{$value}</span>
        </div>
    ";
}
