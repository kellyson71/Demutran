<?php

/**
 * Template de email para formulários de JARI
 * 
 * @param string $subTipo Subtipo do recurso (defesa_previa, jari, apresentacao_condutor)
 * @return array Array contendo título e corpo do email
 */
function getJARIemailContent($subTipo)
{
    switch ($subTipo) {
        case 'defesa_previa':
            $tipoRecurso = "Defesa Prévia";
            break;
        case 'jari':
            $tipoRecurso = "Recurso JARI";
            break;
        case 'apresentacao_condutor':
            $tipoRecurso = "Apresentação de Condutor";
            break;
        default:
            $tipoRecurso = "Formulário de Defesa";
    }

    $titulo = "$tipoRecurso - Análise Concluída";

    $conteudo = <<<HTML
    <p>Seu processo de {$tipoRecurso} foi analisado e processado!</p>
    <p><strong>Próximos Passos:</strong></p>
    <ol style='margin-left: 20px;'>
        <li>Compareça ao DEMUTRAN para receber o resultado</li>
        <li>Traga seu documento de identificação</li>
        <li>O prazo para recursos adicionais, se necessário, é de 30 dias</li>
    </ol>
HTML;

    return [
        'titulo' => $titulo,
        'conteudo' => $conteudo
    ];
}
