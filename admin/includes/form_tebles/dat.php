<?php
function exibirDetalhesDAT($conn, $id)
{
    // Buscar dados de todas as tabelas relacionadas
    $token = null;

    // Primeiro buscar o token do DAT1
    $sql = "SELECT token FROM DAT1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dat = $result->fetch_assoc();
    $token = $dat['token'];

    $tables = [
        'DAT1' => "SELECT * FROM DAT1 WHERE id = ?",
        'DAT2' => "SELECT * FROM DAT2 WHERE id = ?",
        'DAT4' => "SELECT * FROM DAT4 WHERE id = ?",
        'vehicles' => "SELECT 
            uv.id as vehicle_id,
            uv.total_vehicles,
            uv.data_submissao as vehicle_data,
            vd.*
        FROM user_vehicles uv 
        LEFT JOIN vehicle_damages vd ON vd.user_vehicles_id = uv.id 
        WHERE uv.token = ?"
    ];

    $data = [];
    foreach ($tables as $table => $sql) {
        $stmt = $conn->prepare($sql);
        if ($table === 'vehicles') {
            $stmt->bind_param("s", $token);
        } else {
            $stmt->bind_param("i", $id);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($table === 'vehicles') {
            $data[$table] = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $data[$table] = $result->fetch_assoc();
        }
    }

    if (!$data['DAT1']) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">Declaração não encontrada.</span>
              </div>';
        return;
    }

    // Arrays com labels para campos específicos
    $labels = [
        'condicoes_via' => 'Condições da Via',
        'sinalizacao_horizontal_vertical' => 'Sinalização',
        'tracado_via' => 'Traçado da Via',
        'condicoes_meteorologicas' => 'Condições Meteorológicas',
        'tipo_acidente' => 'Tipo do Acidente',
        // Adicione mais labels conforme necessário
    ];

    // Função auxiliar para formatar valores
    function formatValue($key, $value)
    {
        if (empty($value) || $value === 'NULL' || $value === 'não informado') {
            return '<span class="text-gray-400 italic">Não informado</span>';
        }

        if ($value === '1' || $value === 1 || $value === true) {
            return 'Sim';
        }

        if ($value === '0' || $value === 0 || $value === false) {
            return 'Não';
        }

        if (strpos($key, 'data') === 0) {
            return date('d/m/Y', strtotime($value));
        }

        if (strpos($key, 'horario') === 0) {
            return date('H:i', strtotime($value));
        }

        return htmlspecialchars($value);
    }
?>
    <div class="bg-white shadow rounded-lg p-6 space-y-6">
        <!-- Seções principais do DAT -->
        <?php
        // Estrutura das seções e seus campos
        $sections = [
            'Informações do Declarante' => [
                'table' => 'DAT1',
                'fields' => [
                    'nome',
                    'cpf',
                    'relacao_com_veiculo',
                    'profissao',
                    'email',
                    'celular',
                    'data_nascimento',
                    'estrangeiro',
                    'tipo_documento',
                    'numero_documento',
                    'pais',
                    'sexo'
                ]
            ],
            'Endereço do Declarante' => [
                'table' => 'DAT1',
                'fields' => [
                    'cep',
                    'logradouro',
                    'numero',
                    'complemento',
                    'bairro_localidade',
                    'cidade',
                    'uf'
                ]
            ],
            'Local do Acidente' => [
                'table' => 'DAT1',
                'fields' => [
                    'data',
                    'horario',
                    'cidade_acidente',
                    'uf_acidente',
                    'cep_acidente',
                    'logradouro_acidente',
                    'numero_acidente',
                    'complemento_acidente',
                    'bairro_localidade_acidente',
                    'ponto_referencia_acidente'
                ]
            ],
            'Condições do Acidente' => [
                'table' => 'DAT1',
                'fields' => [
                    'condicoes_via',
                    'sinalizacao_horizontal_vertical',
                    'tracado_via',
                    'condicoes_meteorologicas',
                    'tipo_acidente'
                ]
            ],
            'Informações do Veículo' => [
                'table' => 'DAT2',
                'fields' => [
                    'situacao_veiculo',
                    'placa',
                    'renavam',
                    'tipo_veiculo',
                    'chassi',
                    'uf_veiculo',
                    'cor_veiculo',
                    'marca_modelo',
                    'ano_modelo',
                    'ano_fabricacao',
                    'categoria',
                    'segurado',
                    'seguradora',
                    'veiculo_articulado',
                    'manobra_acidente'
                ]
            ],
            'Informações do Condutor' => [
                'table' => 'DAT2',
                'fields' => [
                    'nao_habilitado',
                    'numero_registro',
                    'uf_cnh',
                    'categoria_cnh',
                    'data_1habilitacao',
                    'validade_cnh',
                    'estrangeiro_condutor',
                    'tipo_documento_condutor',
                    'numero_documento_condutor',
                    'pais_documento_condutor',
                    'nome_condutor',
                    'cpf_condutor',
                    'sexo_condutor',
                    'nascimento_condutor',
                    'email_condutor',
                    'celular_condutor'
                ]
            ],
            'Endereço do Condutor' => [
                'table' => 'DAT2',
                'fields' => [
                    'cep_condutor',
                    'logradouro_condutor',
                    'numero_condutor',
                    'complemento_condutor',
                    'bairro_condutor',
                    'cidade_condutor',
                    'uf_condutor'
                ]
            ]
        ];

        foreach ($sections as $title => $config): ?>
            <div class="border-b pb-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4"><?php echo $title; ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($config['fields'] as $field):
                        $label = $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
                        $value = $data[$config['table']][$field] ?? null; ?>
                        <p class="text-gray-600">
                            <span class="font-semibold"><?php echo $label; ?>:</span>
                            <?php echo formatValue($field, $value); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Veículos Envolvidos -->
        <?php if (!empty($data['vehicles'])): ?>
            <div class="border-b pb-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">
                    Veículos Envolvidos (Total: <?php echo count($data['vehicles']); ?>)
                </h3>
                <?php foreach ($data['vehicles'] as $index => $vehicle): ?>
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-lg mb-3 flex items-center">
                            <i class="material-icons mr-2">directions_car</i>
                            Veículo <?php echo $index + 1; ?>
                        </h4>

                        <!-- Partes Danificadas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <h5 class="font-semibold mb-2 text-gray-700">Partes Danificadas:</h5>
                                <div class="space-y-2">
                                    <?php
                                    $partes = [
                                        'dianteira_direita' => 'Dianteira Direita',
                                        'dianteira_esquerda' => 'Dianteira Esquerda',
                                        'lateral_direita' => 'Lateral Direita',
                                        'lateral_esquerda' => 'Lateral Esquerda',
                                        'traseira_direita' => 'Traseira Direita',
                                        'traseira_esquerda' => 'Traseira Esquerda'
                                    ];

                                    $temDanos = false;
                                    foreach ($partes as $key => $label) {
                                        if ($vehicle[$key]) {
                                            $temDanos = true;
                                            echo "<div class='flex items-center text-gray-700'>
                                                    <i class='material-icons text-red-500 text-sm mr-2'>warning</i>
                                                    <span>$label</span>
                                                  </div>";
                                        }
                                    }

                                    if (!$temDanos) {
                                        echo "<p class='text-gray-500 italic'>Nenhuma parte danificada registrada</p>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Informações de Carga -->
                            <div>
                                <h5 class="font-semibold mb-2 text-gray-700">Informações da Carga:</h5>
                                <?php if ($vehicle['has_load_damage']): ?>
                                    <div class="space-y-2">
                                        <p class="text-gray-600">
                                            <span class="font-semibold">Nota Fiscal:</span>
                                            <?php echo htmlspecialchars($vehicle['nota_fiscal'] ?? 'Não informado'); ?>
                                        </p>
                                        <p class="text-gray-600">
                                            <span class="font-semibold">Tipo de Mercadoria:</span>
                                            <?php echo htmlspecialchars($vehicle['tipo_mercadoria'] ?? 'Não informado'); ?>
                                        </p>
                                        <?php if ($vehicle['valor_total']): ?>
                                            <p class="text-gray-600">
                                                <span class="font-semibold">Valor Total:</span>
                                                R$ <?php echo number_format($vehicle['valor_total'], 2, ',', '.'); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($vehicle['estimativa_danos']): ?>
                                            <p class="text-gray-600">
                                                <span class="font-semibold">Estimativa de Danos:</span>
                                                R$ <?php echo number_format($vehicle['estimativa_danos'], 2, ',', '.'); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($vehicle['has_insurance']): ?>
                                            <p class="text-gray-600">
                                                <span class="font-semibold">Seguradora:</span>
                                                <?php echo htmlspecialchars($vehicle['seguradora'] ?? 'Não informada'); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 italic">Não há danos à carga registrados</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Data de Registro -->
                        <div class="mt-4 text-sm text-gray-500">
                            Registrado em: <?php echo date('d/m/Y H:i', strtotime($vehicle['vehicle_data'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="border-b pb-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Veículos Envolvidos</h3>
                <div class="bg-gray-50 rounded-lg p-4 text-gray-500 italic flex items-center justify-center">
                    <i class="material-icons mr-2">info</i>
                    Nenhum veículo registrado
                </div>
            </div>
        <?php endif; ?>

        <!-- Informações Complementares -->
        <?php if ($data['DAT4']): ?>
            <div class="border-b pb-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações Complementares</h3>
                <?php
                $complementares = [
                    'patrimonio_text' => 'Danos ao Patrimônio',
                    'meio_ambiente_text' => 'Danos ao Meio Ambiente',
                    'informacoes_complementares_text' => 'Observações'
                ];
                foreach ($complementares as $field => $label):
                    if (!empty($data['DAT4'][$field]) && $data['DAT4'][$field] !== 'não informado'): ?>
                        <div class="mb-4">
                            <p class="font-semibold text-gray-700 mb-2"><?php echo $label; ?>:</p>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <?php echo nl2br(htmlspecialchars($data['DAT4'][$field])); ?>
                            </div>
                        </div>
                <?php endif;
                endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Status da DAT -->
        <div>
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Status da Declaração</h3>
            <div
                class="inline-flex items-center px-4 py-2 rounded-full 
                <?php echo $data['DAT4']['situacao'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : ($data['DAT4']['situacao'] == 'Aprovado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                <i class="material-icons text-sm mr-2">
                    <?php echo $data['DAT4']['situacao'] == 'Pendente' ? 'pending' : ($data['DAT4']['situacao'] == 'Aprovado' ? 'check_circle' : 'cancel'); ?>
                </i>
                <span class="text-sm font-semibold"><?php echo htmlspecialchars($data['DAT4']['situacao']); ?></span>
            </div>
        </div>
    </div>
<?php
}
?>