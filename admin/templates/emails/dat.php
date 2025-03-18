<?php

/**
 * Template de email para formulários de DAT (Declaração de Acidente de Trânsito)
 * 
 * @param int $id ID do protocolo
 * @return array Array contendo título e corpo do email
 */
function getDATemailContent($id)
{
    $titulo = "DAT - Declaração de Acidente de Trânsito Concluída";

    $conteudo = <<<HTML
    <p>Sua Declaração de Acidente de Trânsito (DAT) foi processada com sucesso!</p>
    <p><strong>Próximos Passos:</strong></p>
    <ol style='margin-left: 20px;'>
        <li>Compareça ao DEMUTRAN para retirar sua DAT</li>
        <li>Traga seu documento de identificação original</li>
        <li>Se possível, traga também o protocolo #{$id}</li>
    </ol>
HTML;

    return [
        'titulo' => $titulo,
        'conteudo' => $conteudo
    ];
}
