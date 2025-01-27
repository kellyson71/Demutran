<?php
session_start();
require_once '../../env/config.php';
require_once '../../components/print-components.php';

// Debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obter ID do DAT
$id = $_GET['id'] ?? '';

if (!$id) {
    die('ID não fornecido');
}

// Buscar todos os dados relacionados ao DAT usando as tabelas corretas
$sql = "
SELECT 
    d1.*, 
    d2.*, 
    d4.*,
    uv.*,
    vd.*,
    d4.informacoes_complementares_text,
    d4.meio_ambiente_text,
    d4.patrimonio_text
FROM DAT4 d4
LEFT JOIN DAT1 d1 ON d1.token = d4.token
LEFT JOIN DAT2 d2 ON d2.token = d4.token
LEFT JOIN user_vehicles uv ON uv.token = d4.token
LEFT JOIN vehicle_damages vd ON vd.user_vehicles_id = uv.id
WHERE d4.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$dados = $result->fetch_assoc();

if (!$dados) {
    die('DAT não encontrado');
}

// Buscar veículos adicionais
$sql_veiculos = "
    SELECT vd.* 
    FROM user_vehicles uv 
    JOIN vehicle_damages vd ON vd.user_vehicles_id = uv.id 
    WHERE uv.token = ?";
$stmt = $conn->prepare($sql_veiculos);
$stmt->bind_param('s', $dados['token']);
$stmt->execute();
$veiculos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Paths corretos para as imagens
$imagePath = '../../assets/';

// Função de formatação
function formatValue($field, $value)
{
    // Campos booleanos
    $booleanFields = ['estrangeiro', 'segurado', 'nao_habilitado', 'veiculo_articulado', 'has_insurance'];
    if (in_array($field, $booleanFields)) {
        return $value ? 'Sim' : 'Não';
    }

    // Campos de data
    if (strpos($field, 'data_') !== false && $value) {
        return date('d/m/Y H:i:s', strtotime($value));
    }

    // Campos monetários
    $moneyFields = ['valor_total', 'estimativa_danos', 'valor_mercadoria'];
    if (in_array($field, $moneyFields) && $value) {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    // Campos de texto longo
    $textFields = ['informacoes_complementares_text', 'meio_ambiente_text', 'patrimonio_text'];
    if (in_array($field, $textFields)) {
        return nl2br(htmlspecialchars($value));
    }

    // Campos específicos
    $specificFields = [
        'sexo' => ['M' => 'Masculino', 'F' => 'Feminino'],
        'condicoes_via' => [
            'seca' => 'Seca',
            'molhada' => 'Molhada',
            'oleosa' => 'Oleosa'
        ],
        // Adicione mais mapeamentos conforme necessário
    ];

    if (isset($specificFields[$field])) {
        return $specificFields[$field][$value] ?? $value;
    }

    // Tratamento especial para damaged_parts
    if ($field === 'damaged_parts' && !empty($value)) {
        global $mapeamentoPartesDanificadas;
        $partes = json_decode($value, true);
        if (is_array($partes)) {
            $partesDanificadas = [];
            foreach ($partes as $parte) {
                if ($parte['checked']) {
                    // Remove números do final do nome e procura no mapeamento
                    $nomeParte = preg_replace('/\_\d+$/', '', $parte['name']);
                    if (isset($mapeamentoPartesDanificadas[$nomeParte])) {
                        $partesDanificadas[] = $mapeamentoPartesDanificadas[$nomeParte];
                    }
                }
            }
            return !empty($partesDanificadas) ? implode(', ', $partesDanificadas) : 'Nenhuma parte danificada';
        }
    }

    // Campos booleanos de danos
    $booleanDamageFields = [
        'dianteira_direita',
        'dianteira_esquerda',
        'lateral_direita',
        'lateral_esquerda',
        'traseira_direita',
        'traseira_esquerda',
        'has_load_damage',
        'has_insurance'
    ];

    if (in_array($field, $booleanDamageFields)) {
        return $value ? 'Sim' : 'Não';
    }

    return $value;
}

// Definir grupos de campos logo após a consulta SQL e antes do HTML
$gruposDAT1 = [
    'Dados Pessoais' => [
        'nome' => 'Nome',
        'cpf' => 'CPF',
        'data_nascimento' => 'Data de Nascimento',
        'sexo' => 'Sexo',
        'profissao' => 'Profissão',
        'email' => 'Email',
        'celular' => 'Telefone',
        'estrangeiro' => 'Estrangeiro',
        'tipo_documento' => 'Tipo de Documento',
        'numero_documento' => 'Número do Documento',
        'pais' => 'País',
        'relacao_com_veiculo' => 'Relação com Veículo'
    ],
    'Endereço do Declarante' => [
        'logradouro' => 'Logradouro',
        'numero' => 'Número',
        'complemento' => 'Complemento',
        'bairro_localidade' => 'Bairro',
        'cidade' => 'Cidade',
        'uf' => 'UF',
        'cep' => 'CEP'
    ],
    'Local do Acidente' => [
        'data' => 'Data',
        'horario' => 'Horário',
        'logradouro_acidente' => 'Logradouro',
        'numero_acidente' => 'Número',
        'complemento_acidente' => 'Complemento',
        'bairro_localidade_acidente' => 'Bairro',
        'cidade_acidente' => 'Cidade',
        'uf_acidente' => 'UF',
        'cep_acidente' => 'CEP',
        'ponto_referencia_acidente' => 'Ponto de Referência'
    ],
    'Condições do Acidente' => [
        'tipo_acidente' => 'Tipo de Acidente',
        'condicoes_via' => 'Condições da Via',
        'sinalizacao_horizontal_vertical' => 'Sinalização',
        'tracado_via' => 'Traçado da Via',
        'condicoes_meteorologicas' => 'Condições Meteorológicas'
    ]
];

$gruposDAT2 = [
    'Dados do Veículo' => [
        'placa' => 'Placa',
        'renavam' => 'RENAVAM',
        'chassi' => 'Chassi',
        'marca_modelo' => 'Marca/Modelo',
        'tipo_veiculo' => 'Tipo de Veículo',
        'cor_veiculo' => 'Cor',
        'ano_fabricacao' => 'Ano Fabricação',
        'ano_modelo' => 'Ano Modelo',
        'uf_veiculo' => 'UF',
        'situacao_veiculo' => 'Situação',
        'veiculo_articulado' => 'Articulado',
        'segurado' => 'Segurado',
        'seguradora' => 'Seguradora',
        'manobra_acidente' => 'Manobra durante Acidente',
        'danos_sistema_seguranca' => 'Danos no Sistema de Segurança',
        'danos_carga' => 'Danos na Carga',
        'extensao_danos' => 'Extensão dos Danos',
        'tem_seguro_carga' => 'Possui Seguro de Carga',
        'seguradora_carga' => 'Seguradora da Carga',
        'numero_notas' => 'Número de Notas',
        'tipo_mercadoria' => 'Tipo de Mercadoria',
        'valor_mercadoria' => 'Valor da Mercadoria',
        'partes_danificadas' => 'Partes Danificadas'
    ],
    'Dados do Condutor' => [
        'nome_condutor' => 'Nome',
        'cpf_condutor' => 'CPF',
        'sexo_condutor' => 'Sexo',
        'nascimento_condutor' => 'Data de Nascimento',
        'email_condutor' => 'Email',
        'celular_condutor' => 'Telefone',
        'numero_registro' => 'CNH',
        'categoria_cnh' => 'Categoria',
        'uf_cnh' => 'UF CNH',
        'data_1habilitacao' => '1ª Habilitação',
        'validade_cnh' => 'Validade CNH',
        'nao_habilitado' => 'Não Habilitado',
        'estrangeiro_condutor' => 'Estrangeiro',
        'tipo_documento_condutor' => 'Tipo de Documento',
        'numero_documento_condutor' => 'Número do Documento',
        'pais_documento_condutor' => 'País'
    ],
    'Endereço do Condutor' => [
        'logradouro_condutor' => 'Logradouro',
        'numero_condutor' => 'Número',
        'complemento_condutor' => 'Complemento',
        'bairro_condutor' => 'Bairro',
        'cidade_condutor' => 'Cidade',
        'uf_condutor' => 'UF',
        'cep_condutor' => 'CEP'
    ]
];

$gruposVehicles = [
    'Danos e Avarias' => [
        'dianteira_direita' => 'Dianteira Direita',
        'dianteira_esquerda' => 'Dianteira Esquerda',
        'lateral_direita' => 'Lateral Direita',
        'lateral_esquerda' => 'Lateral Esquerda',
        'traseira_direita' => 'Traseira Direita',
        'traseira_esquerda' => 'Traseira Esquerda',
        'has_load_damage' => 'Danos à Carga',
        'estimativa_danos' => 'Estimativa de Danos'
    ],
    'Informações da Carga' => [
        'nota_fiscal' => 'Nota Fiscal',
        'tipo_mercadoria' => 'Tipo de Mercadoria',
        'valor_total' => 'Valor Total'
    ],
    'Seguro' => [
        'has_insurance' => 'Possui Seguro',
        'seguradora' => 'Seguradora'
    ]
];

$mapeamentoPartesDanificadas = [
    'parte_danificada_dianteira_direita' => 'Dianteira Direita',
    'parte_danificada_dianteira_esquerda' => 'Dianteira Esquerda',
    'parte_danificada_lateral_direita' => 'Lateral Direita',
    'parte_danificada_lateral_esquerda' => 'Lateral Esquerda',
    'parte_danificada_traseira_direita' => 'Traseira Direita',
    'parte_danificada_traseira_esquerda' => 'Traseira Esquerda'
];

// Adicionar ao array de grupos após os grupos existentes
$gruposDAT4 = [
    'Informações Complementares' => [
        'informacoes_complementares_text' => 'Detalhes do Acidente',
        'meio_ambiente_text' => 'Impacto Ambiental',
        'patrimonio_text' => 'Danos ao Patrimônio',
        'data_submissao' => 'Data de Submissão'
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Declaração de Acidente de Trânsito</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            margin: 20px 0;
            background: white;
        }

        .container {
            max-width: 800px;
            background: white;
            padding: 20px;
            margin: 0 auto;
        }

        .logo-container {
            position: relative;
            height: 120px;
            margin-bottom: 40px;
        }

        .logo {
            position: absolute;
            top: 0;
            max-width: 80px;
            height: auto;
        }

        .logo-left {
            left: 0;
        }

        .logo-right {
            right: 0;
        }

        .centered-title {
            text-align: center;
            padding: 0 100px;
            margin-top: 20px;
        }

        .section-title {
            background-color: #E3F2FD;
            padding: 8px 15px;
            margin: 20px 0 10px 0;
            font-weight: bold;
            border-left: 4px solid #2196F3;
        }

        .data-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 15px;
        }

        .data-row {
            flex: 1 1 300px;
            min-width: 300px;
            display: flex;
            align-items: baseline;
            gap: 10px;
        }

        .data-row .label {
            white-space: nowrap;
            min-width: 120px;
        }

        .data-row .value {
            flex: 1;
        }

        .text-block {
            flex: 1 1 100%;
        }

        @media print {
            body {
                margin: 0;
            }

            .container {
                width: 100%;
                max-width: none;
                padding: 15px;
            }
        }

        .document-section {
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .section-content {
            padding: 20px;
            background: #fff;
            border-radius: 4px;
        }

        .info-group {
            margin-bottom: 1.5rem;
        }

        .info-group h6 {
            color: #2196F3;
            border-bottom: 2px solid #E3F2FD;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .info-row {
            display: inline-block;
            margin-right: 30px;
            margin-bottom: 10px;
        }

        .info-label {
            color: #666;
            font-size: 0.9em;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .text-block {
            margin-bottom: 1rem;
        }

        .text-content {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
            line-height: 1.6;
        }

        .signatures-container {
            margin-top: 3rem;
            padding: 20px;
        }

        .signature-block {
            margin: 2rem 0;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            width: 300px;
            margin: 10px auto;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 3rem;
        }

        .signature-info {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>

<body>
    <?php echo renderPrintComponents(); ?>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="logo-container">
            <img src="./image1.png" alt="Logo Esquerda" class="logo logo-left">
            <img src="./image3.png" alt="Logo Direita" class="logo logo-right">
            <div class="centered-title">
                <p>Estado do Rio Grande do Norte</p>
                <p>Prefeitura Municipal de Pau dos Ferros</p>
                <p>Secretaria de Governo – SEGOV</p>
                <p>Departamento Municipal de Trânsito – DEMUTRAN</p>
            </div>
        </div>

        <div class="document-section">
            <div class="section-title">DECLARAÇÃO DE ACIDENTE DE TRÂNSITO</div>
            <div class="section-content">
                <?php foreach ($gruposDAT1 as $titulo => $campos): ?>
                    <div class="info-group">
                        <h6><?php echo $titulo; ?></h6>
                        <?php foreach ($campos as $campo => $label):
                            if (isset($dados[$campo])) {
                                $value = formatValue($campo, $dados[$campo]);
                                if ($value !== '' && $value !== null) { ?>
                                    <div class="info-row">
                                        <div class="info-label"><?php echo $label; ?></div>
                                        <div class="info-value"><?php echo $value; ?></div>
                                    </div>
                        <?php }
                            }
                        endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="document-section">
            <div class="section-title">INFORMAÇÕES DO VEÍCULO E CONDUTOR</div>
            <div class="section-content">
                <?php foreach ($gruposDAT2 as $titulo => $campos): ?>
                    <div class="info-group">
                        <h6><?php echo $titulo; ?></h6>
                        <?php foreach ($campos as $campo => $label):
                            if (isset($dados[$campo])) {
                                $value = formatValue($campo, $dados[$campo]);
                                if ($value !== '' && $value !== null) { ?>
                                    <div class="info-row">
                                        <div class="info-label"><?php echo $label; ?></div>
                                        <div class="info-value"><?php echo $value; ?></div>
                                    </div>
                        <?php }
                            }
                        endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($veiculos)): ?>
            <div class="document-section">
                <div class="section-title">OUTROS VEÍCULOS ENVOLVIDOS</div>
                <div class="section-content">
                    <?php foreach ($veiculos as $index => $veiculo): ?>
                        <div class="info-group">
                            <h6>Veículo <?php echo $index + 2; ?></h6>
                            <?php
                            $outrosVeiculosFields = [
                                'damage_system' => 'Sistema de Danos',
                                'damaged_parts' => 'Partes Danificadas',
                                'load_damage' => 'Danos à Carga',
                                'estimativa_danos' => 'Danos Estimados',
                                'nota_fiscal' => 'Nota Fiscal',
                                'tipo_mercadoria' => 'Tipo de Mercadoria',
                                'valor_total' => 'Valor Total',
                                'has_insurance' => 'Possui Seguro',
                                'seguradora' => 'Seguradora'
                            ];

                            foreach ($outrosVeiculosFields as $field => $label) {
                                if (isset($veiculo[$field]) && !empty($veiculo[$field])) {
                                    $value = formatValue($field, $veiculo[$field]);
                                    if ($value !== '' && $value !== null) {
                                        echo "<div class='info-row'>";
                                        echo "<div class='info-label'>$label</div>";
                                        echo "<div class='info-value'>$value</div>";
                                        echo "</div>";
                                    }
                                }
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Informações Complementares -->
        <div class="document-section">
            <div class="section-title">INFORMAÇÕES COMPLEMENTARES</div>
            <div class="section-content">
                <?php foreach ($gruposDAT4 as $titulo => $campos): ?>
                    <div class="info-group">
                        <?php foreach ($campos as $campo => $label):
                            if (isset($dados[$campo]) && !empty($dados[$campo])) {
                                $value = formatValue($campo, $dados[$campo]);
                                if ($value !== '' && $value !== null) {
                                    if (in_array($campo, ['informacoes_complementares_text', 'meio_ambiente_text', 'patrimonio_text'])) { ?>
                                        <div class="text-block">
                                            <h6><?php echo $label; ?></h6>
                                            <div class="text-content">
                                                <?php echo $value; ?>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="info-row">
                                            <div class="info-label"><?php echo $label; ?></div>
                                            <div class="info-value"><?php echo $value; ?></div>
                                        </div>
                        <?php }
                                }
                            }
                        endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Assinaturas -->
        <div class="signatures-container">
            <p class="text-center mb-4">Pau dos Ferros/RN, <?php echo date('d/m/Y'); ?></p>

            <div class="signatures-grid">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p class="mb-0">Assinatura do Declarante</p>
                    <p class="signature-info">
                        <?php echo htmlspecialchars($dados['nome'] ?? ''); ?><br>
                        CPF: <?php echo htmlspecialchars($dados['cpf'] ?? ''); ?>
                    </p>
                </div>

                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p class="mb-0">Responsável DEMUTRAN</p>
                    <p class="signature-info">
                        Departamento Municipal de Trânsito<br>
                        Matrícula: _____________
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($_GET['print'] ?? false): ?>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    <?php endif; ?>
</body>

</html>