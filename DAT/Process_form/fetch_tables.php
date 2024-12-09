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

if (!empty($results)) {
    echo "<div class='max-w-6xl mx-auto p-6 space-y-8'>"; // Aumentado para max-w-6xl
    
    foreach ($results as $table => $rows) {
        echo "<div class='bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100'>";
        // Novo cabeçalho mais suave
        echo "<div class='bg-gray-50 border-b border-gray-200 p-4 flex items-center space-x-3'>";
        echo "<svg class='w-5 h-5 text-gray-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' />
              </svg>";
        echo "<span class='text-lg text-gray-700 font-medium'>Formulário {$table}</span>";
        echo "</div>";
        
        echo "<div class='max-h-[700px] overflow-y-auto'>";  // Aumentada altura máxima
        echo "<table class='w-full table-fixed'><tbody>";
        
        foreach (array_keys($rows[0]) as $column) {
            if ($column !== 'token' && $column !== 'id') {
                $columnName = $columnNames[$table][$column] ?? $column;
                $value = $rows[0][$column];
                
                echo "<tr class='border-b border-gray-100 transition-colors hover:bg-blue-50'>";
                echo "<td class='py-4 px-6 font-medium text-gray-700 w-2/5 bg-gray-50'>";
                echo "<div class='flex items-center space-x-2'>";
                echo "<span class='text-blue-600'>•</span>";
                echo "<span>{$columnName}</span>";
                echo "</div>";
                echo "</td>";
                
                echo "<td class='py-4 px-6 text-gray-600'>";
                if ($table === 'vehicles' && $column === 'damaged_parts') {
                    echo displayDamagedParts($value);
                } else {
                    $displayValue = displayBinaryValue($value);
                    // Adiciona classes especiais para Sim/Não
                    if ($displayValue === 'Sim') {
                        echo "<span class='px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium'>Sim</span>";
                    } else if ($displayValue === 'Não') {
                        echo "<span class='px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm font-medium'>Não</span>";
                    } else {
                        echo "<span class='text-gray-700'>{$displayValue}</span>";
                    }
                }
                echo "</td>";
                echo "</tr>";
            }
        }
        
        echo "</tbody></table>";
        echo "</div></div>";
    }
    echo "</div>";

    echo "<style>
        .max-h-[700px]::-webkit-scrollbar {
            width: 10px;
        }
        
        .max-h-[700px]::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .max-h-[700px]::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
            border: 2px solid #f8fafc;
        }
        
        .max-h-[700px]::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Animação suave no hover */
        .transition-colors {
            transition: all 0.2s ease;
        }
        
        /* Para Firefox */
        .max-h-[700px] {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        
        /* Sombra suave nos cards */
        .shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>";
} else {
    echo "<div class='max-w-6xl mx-auto p-6'>
            <div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md'>
                <div class='flex items-center'>
                    <svg class='h-6 w-6 text-red-500 mr-3' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' />
                    </svg>
                    <p class='font-medium'>Nenhum resultado encontrado.</p>
                </div>
            </div>
          </div>";
}

$conn->close();
?>