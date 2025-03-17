<?php
return [
    'tabela' => 'DAT4',
    'campos' => [
        ['nome' => 'patrimonio_text', 'tipo' => 'string', 'obrigatorio' => true],
        ['nome' => 'meio_ambiente_text', 'tipo' => 'string', 'obrigatorio' => true],
        ['nome' => 'informacoes_complementares_text', 'tipo' => 'string', 'obrigatorio' => true],
    ],
    'status_apos_processo' => 'completo',
    'enviar_email' => true,
    'verificar_existente' => false
];
