<?php

/**
 * Template de email para formulários de Parecer Técnico
 * 
 * @param int $id ID do protocolo
 * @return array Array contendo título e corpo do email
 */
function getParecerEmailContent($id)
{
    $titulo = "Parecer Técnico - Documento Disponível";

    $conteudo = <<<HTML
    <p>Seu Parecer Técnico foi elaborado e está disponível!</p>
    <p><strong>Instruções:</strong></p>
    <ol style='margin-left: 20px;'>
        <li>O documento deve ser retirado pessoalmente no DEMUTRAN</li>
        <li>Apresente documento de identificação e o protocolo #{$id}</li>
        <li>O parecer tem validade de 90 dias após a emissão</li>
    </ol>
HTML;

    return [
        'titulo' => $titulo,
        'conteudo' => $conteudo
    ];
}
