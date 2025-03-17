<?php
return [
    'tabela' => 'user_vehicles',
    'campos' => [
        ['nome' => 'total_vehicles', 'tipo' => 'integer', 'obrigatorio' => true],
    ],
    // Essa configuração é especial e precisará de processamento específico para os dados de veículos
    'tabela_relacionada' => 'vehicle_damages',
    'campos_relacionados' => [
        ['nome' => 'user_vehicles_id', 'tipo' => 'integer', 'obrigatorio' => true],
        ['nome' => 'dianteira_direita', 'tipo' => 'boolean', 'padrao' => false],
        ['nome' => 'dianteira_esquerda', 'tipo' => 'boolean', 'padrao' => false],
        ['nome' => 'lateral_direita', 'tipo' => 'boolean', 'padrao' => false],
        ['nome' => 'lateral_esquerda', 'tipo' => 'boolean', 'padrao' => false],
        ['nome' => 'traseira_direita', 'tipo' => 'boolean', 'padrao' => false],
        ['nome' => 'traseira_esquerda', 'tipo' => 'boolean', 'padrao' => false],
        ['nome' => 'has_load_damage', 'tipo' => 'boolean', 'padrao' => false],
        ['nome' => 'nota_fiscal', 'tipo' => 'string', 'obrigatorio' => false],
        ['nome' => 'tipo_mercadoria', 'tipo' => 'string', 'obrigatorio' => false],
        ['nome' => 'valor_total', 'tipo' => 'float', 'obrigatorio' => false],
        ['nome' => 'estimativa_danos', 'tipo' => 'float', 'obrigatorio' => false],
        ['nome' => 'has_insurance', 'tipo' => 'boolean', 'padrao' => false],
        ['nome' => 'seguradora', 'tipo' => 'string', 'obrigatorio' => false],
    ],
    'status_apos_processo' => 'Em Andamento',
    'enviar_email' => false,
    'verificar_existente' => true,
    'json_input' => true // Indica que a entrada é JSON e não POST
];
