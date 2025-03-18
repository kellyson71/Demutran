<?php

/**
 * Template de email para formulários de PCD/Idoso
 * 
 * @param string $subTipo Subtipo da solicitação (pcd ou idoso)
 * @return array Array contendo título e corpo do email
 */
function getPCDemailContent($subTipo)
{
    $tipoCartao = ($subTipo === 'idoso') ? 'Idoso' : 'PCD';

    $titulo = "Cartão de Estacionamento $tipoCartao - Solicitação Concluída";

    $conteudo = <<<HTML
    <p>Sua solicitação do Cartão de Estacionamento $tipoCartao foi aprovada!</p>
    <p><strong>Próximos Passos:</strong></p>
    <ol style='margin-left: 20px;'>
        <li>Compareça ao DEMUTRAN para retirar seu cartão</li>
        <li>Traga os seguintes documentos:</li>
        <ul style='margin-left: 40px;'>
            <li>Documento de identificação original</li>
            <li>Laudo médico original</li>
            <li>Comprovante de residência</li>
        </ul>
    </ol>
HTML;

    return [
        'titulo' => $titulo,
        'conteudo' => $conteudo
    ];
}
