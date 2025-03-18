<?php

/**
 * Template base para todos os emails
 * 
 * @param string $titulo Título do email
 * @param string $nome Nome do destinatário
 * @param string $conteudo Conteúdo específico do email
 * @return string HTML completo do email
 */
function getEmailTemplate($titulo, $nome, $conteudo)
{
    return <<<HTML
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <div style='background-color: #f5f5f5; padding: 20px;'>
            <h2 style='color: #2c5282;'>{$titulo}</h2>
            <p>Prezado(a) {$nome},</p>
            {$conteudo}
            <div style='background-color: #ffffff; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                <p><strong>Canais de Atendimento DEMUTRAN:</strong></p>
                <p>📞 Telefone: (84) 3351-2868</p>
                <p>📧 E-mail: demutran@paudosferros.rn.gov.br</p>
                <p>📍 Endereço: Av. Getúlio Vargas, 1323, Centro, Pau dos Ferros-RN</p>
            </div>
        </div>
    </body>
    </html>
HTML;
}
