<?php

/**
 * Template de email para formulários de SAC
 * 
 * @param int $id ID do protocolo
 * @return array Array contendo título e corpo do email
 */
function getSACemailContent($id)
{
    $titulo = "Atendimento SAC - Solicitação Processada";

    $conteudo = <<<HTML
    <p>Sua solicitação ao SAC foi processada!</p>
    <p><strong>Informações:</strong></p>
    <ul style='margin-left: 20px;'>
        <li>Protocolo: #{$id}</li>
        <li>Status: Concluído</li>
    </ul>
    <p>Caso necessite de informações adicionais, entre em contato com nossos canais de atendimento.</p>
HTML;

    return [
        'titulo' => $titulo,
        'conteudo' => $conteudo
    ];
}
