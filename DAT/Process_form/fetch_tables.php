<?php
include '../scr/config.php';

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obtém o token via POST
$token = $_POST['token'];

// Consultas nas tabelas 'DAT1', 'DAT2' e 'vehicles'
$tables = ['DAT1', 'DAT2', 'vehicles'];
$results = [];

// Mapeamento de colunas para nomes amigáveis
$columnNames = [
    'vehicles' => [
        'damage_system' => 'Teve parte danificada?',
        'damaged_parts' => 'Partes danificadas',
        'load_damage' => 'Danos à carga',
        'nota_fiscal' => 'Nota Fiscal',
        'tipo_mercadoria' => 'Tipo de Mercadoria',
        'valor_total' => 'Valor Total',
        'estimativa_danos' => 'Estimativa de Danos',
        'has_insurance' => 'Possui seguro?',
        'seguradora' => 'Seguradora',
        'created_at' => 'Criado em'
    ],
    'DAT2' => [
        'situacao_veiculo' => 'Situação do Veículo',
        'placa' => 'Placa',
        'renavam' => 'Renavam',
        'tipo_veiculo' => 'Tipo de Veículo',
        'chassi' => 'Chassi',
        'uf_veiculo' => 'UF do Veículo',
        'cor_veiculo' => 'Cor do Veículo',
        'marca_modelo' => 'Marca/Modelo',
        'ano_modelo' => 'Ano do Modelo',
        'ano_fabricacao' => 'Ano de Fabricação',
        'categoria' => 'Categoria',
        'segurado' => 'Segurado?',
        'seguradora' => 'Seguradora',
        'veiculo_articulado' => 'Veículo Articulado?',
        'manobra_acidente' => 'Manobra no Acidente',
        'nao_habilitado' => 'Condutor não Habilitado?',
        'numero_registro' => 'Número de Registro',
        'uf_cnh' => 'UF da CNH',
        'categoria_cnh' => 'Categoria CNH',
        'data_1habilitacao' => 'Data da 1ª Habilitação',
        'validade_cnh' => 'Validade da CNH',
        'estrangeiro_condutor' => 'Condutor Estrangeiro?',
        'tipo_documento_condutor' => 'Tipo de Documento do Condutor',
        'numero_documento_condutor' => 'Número do Documento do Condutor',
        'pais_documento_condutor' => 'País do Documento do Condutor',
        'nome_condutor' => 'Nome do Condutor',
        'cpf_condutor' => 'CPF do Condutor',
        'sexo_condutor' => 'Sexo do Condutor',
        'nascimento_condutor' => 'Data de Nascimento do Condutor',
        'email_condutor' => 'Email do Condutor',
        'celular_condutor' => 'Celular do Condutor',
        'cep_condutor' => 'CEP do Condutor',
        'logradouro_condutor' => 'Logradouro do Condutor',
        'numero_condutor' => 'Número do Condutor',
        'complemento_condutor' => 'Complemento do Condutor',
        'bairro_condutor' => 'Bairro do Condutor',
        'cidade_condutor' => 'Cidade do Condutor',
        'uf_condutor' => 'UF do Condutor',
        'danos_sistema_seguranca' => 'Danos no Sistema de Segurança',
        'partes_danificadas' => 'Partes Danificadas',
        'danos_carga' => 'Danos à Carga',
        'numero_notas' => 'Número de Notas',
        'tipo_mercadoria' => 'Tipo de Mercadoria',
        'valor_mercadoria' => 'Valor da Mercadoria',
        'extensao_danos' => 'Extensão dos Danos',
        'tem_seguro_carga' => 'Possui Seguro de Carga?',
        'seguradora_carga' => 'Seguradora da Carga'
    ],
    'DAT1' => [
        'relacao_com_veiculo' => 'Relação com o Veículo',
        'estrangeiro' => 'É Estrangeiro?',
        'tipo_documento' => 'Tipo de Documento',
        'numero_documento' => 'Número do Documento',
        'pais' => 'País',
        'nome' => 'Nome',
        'cpf' => 'CPF',
        'profissao' => 'Profissão',
        'sexo' => 'Sexo',
        'data_nascimento' => 'Data de Nascimento',
        'email' => 'Email',
        'celular' => 'Celular',
        'cep' => 'CEP',
        'logradouro' => 'Logradouro',
        'numero' => 'Número',
        'complemento' => 'Complemento',
        'bairro_localidade' => 'Bairro/Localidade',
        'cidade' => 'Cidade',
        'uf' => 'UF',
        'data' => 'Data',
        'horario' => 'Horário',
        'cidade_acidente' => 'Cidade do Acidente',
        'uf_acidente' => 'UF do Acidente',
        'cep_acidente' => 'CEP do Acidente',
        'logradouro_acidente' => 'Logradouro do Acidente',
        'numero_acidente' => 'Número do Acidente',
        'complemento_acidente' => 'Complemento do Acidente',
        'bairro_localidade_acidente' => 'Bairro/Localidade do Acidente',
        'ponto_referencia_acidente' => 'Ponto de Referência do Acidente',
        'condicoes_via' => 'Condições da Via',
        'sinalizacao_horizontal_vertical' => 'Sinalização Horizontal/Vertical',
        'tracado_via' => 'Traçado da Via',
        'condicoes_meteorologicas' => 'Condições Meteorológicas',
        'tipo_acidente' => 'Tipo de Acidente'
    ]
];

// Percorre cada tabela e coleta os resultados
foreach ($tables as $table) {
    $sql = "SELECT * FROM $table WHERE token = '$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $results[$table] = $rows;
    }
}

// Função para tratar e exibir a coluna damaged_parts
function displayDamagedParts($json) {
    $parts = json_decode($json, true);
    $output = "<ul class='list-disc pl-5'>";
    foreach ($parts as $part) {
        $status = $part['checked'] ? 'DANIFICADA' : 'INTEIRA';
        $output .= "<li>" . $part['name'] . ": " . $status . "</li>";
    }
    $output .= "</ul>";
    return $output;
}

// Função para exibir "Sim" ou "Não" em colunas binárias
function displayBinaryValue($value) {
    return $value == 1 ? 'Sim' : ($value == 0 ? 'Não' : $value);
}

// Verifica se há resultados
if (!empty($results)) {
    // Percorre cada tabela para exibir seus dados
    foreach ($results as $table => $rows) {
        echo "<h3 class='text-xl font-semibold mb-4'>Formulario: $table</h3>";

        // Adiciona a classe overflow-x-auto para rolagem horizontal
        echo "<div class='overflow-x-auto'>";
        echo "<table class='min-w-full bg-white border border-gray-300'><thead>";
        echo "<tr class='bg-gray-100 border-b'>";

        // Exibe os cabeçalhos das colunas (omitindo 'token' e 'id')
        foreach (array_keys($rows[0]) as $column) {
            if ($column !== 'token' && $column !== 'id') {
                $columnName = $columnNames[$table][$column] ?? $column; // Usa o nome amigável ou o original
                echo "<th class='text-left p-4 border-b-2 border-gray-300'>$columnName</th>";
            }
        }
        echo "</tr></thead><tbody>";

        // Exibe os dados de cada linha
        foreach ($rows as $row) {
            echo "<tr class='border-b'>";
            foreach ($row as $key => $cell) {
                // Ignora as colunas 'token' e 'id'
                if ($key === 'token' || $key === 'id') {
                    continue;
                }
                // Tratamento especial para a coluna damaged_parts
                if ($table === 'vehicles' && $key === 'damaged_parts') {
                    echo "<td class='p-4 border-t border-gray-300'>" . displayDamagedParts($cell) . "</td>";
                } else {
                    // Exibe "Sim" ou "Não" para colunas binárias
                    echo "<td class='p-4 border-t border-gray-300'>" . displayBinaryValue($cell) . "</td>";
                }
            }
            echo "</tr>";
        }

        echo "</tbody></table></div>";
    }
} else {
    echo "Nenhum resultado encontrado.";
}

$conn->close();
?>