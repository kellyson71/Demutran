<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Formulário de Defesa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Copiado do gerar_formulario2.php */
        body {
            margin-top: 20px;
        }

        .container {
            max-width: 800px;
        }

        /* Estilo do cabeçalho e logos */
        .logo-container {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }

        .logo {
            width: 80px;
            height: auto;
        }

        .centered-title {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            text-align: center;
        }

        /* Estilização da tabela de dados */
        .data-table {
            width: 100%;
            margin-top: 20px;
            border-spacing: 4px;
        }

        .data-table td {
            padding: 8px;
            vertical-align: top;
        }

        /* Estilo para campos não informados */
        .not-informed {
            color: #999;
            font-style: italic;
            position: relative;
            padding-left: 20px;
        }

        .not-informed::before {
            content: "•";
            color: #dc3545;
            position: absolute;
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Estilo para o título da seção */
        .section-title {
            background-color: #E3F2FD;
            padding: 10px;
            margin-top: 20px;
            font-weight: bold;
            border-left: 4px solid #2196F3;
        }

        /* Estilos específicos para defesas */
        .defesa-header {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            border-left: 4px solid #0d6efd;
        }

        /* Estilos específicos para cada tipo de defesa */
        .apresentacao-condutor .section-title {
            background-color: #e3f2fd;
            border-left: 4px solid #1976d2;
        }

        .defesa-previa .section-title {
            background-color: #fff3e0;
            border-left: 4px solid #f57c00;
        }

        .recurso-jari .section-title {
            background-color: #e8f5e9;
            border-left: 4px solid #388e3c;
        }

        /* Estilos para campos específicos */
        [data-field-type="auto_infracao"] {
            background-color: #ffebee;
            font-weight: bold;
        }

        [data-field-type="veiculo"] {
            background-color: #e8eaf6;
        }

        [data-field-type="condutor"] {
            background-color: #e0f2f1;
        }

        [data-field-type="documentos"] {
            background-color: #fff3e0;
        }

        /* Estilo para anexos/documentos */
        .document-container {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .document-item {
            flex: 1;
            min-width: 200px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>

<body>
    <?php
    require_once '../../components/print-components.php';
    echo renderPrintComponents();
    ?>
    <div class="container">
        <div class="logo-container">
            <img src="image1.png" alt="Logo Esquerda" class="logo logo-left">
            <img src="image3.png" alt="Logo Direita" class="logo logo-right">
            <div class="centered-title text-center">
                <p>Estado do Rio Grande do Norte</p>
                <p>Prefeitura Municipal de Pau dos Ferros</p>
                <p>Secretaria de Governo – SEGOV</p>
                <p>Departamento Municipal de Trânsito – DEMUTRAN</p>
            </div>
        </div>

        <h3 class="text-center mt-3">
            <?php
            $tipos = [
                'apresentacao_condutor' => 'FORMULÁRIO DE INDICAÇÃO DE CONDUTOR INFRATOR',
                'defesa_previa' => 'FORMULÁRIO DE DEFESA PRÉVIA',
                'jari' => 'FORMULÁRIO DE RECURSO JARI'
            ];
            echo $tipos[$dados['tipo_solicitacao']] ?? 'FORMULÁRIO DE DEFESA';
            ?>
        </h3>

        <div class="content">
            <div class="section-title">DADOS DA INFRAÇÃO</div>
            <table class="data-table">
                <?php
                $campos_infracao = [
                    'autoInfracao' => 'Auto de Infração',
                    'placa' => 'Placa',
                    'marcaModelo' => 'Marca/Modelo',
                    'cor' => 'Cor',
                    'especie' => 'Espécie',
                    'categoria' => 'Categoria',
                    'ano' => 'Ano',
                    'dataInfracao' => 'Data da Infração',
                    'horaInfracao' => 'Hora da Infração',
                    'localInfracao' => 'Local da Infração',
                    'enquadramento' => 'Enquadramento'
                ];

                foreach ($campos_infracao as $campo => $rotulo) {
                    if (isset($dados[$campo]) && !strpos($campo, '_url')) {
                        $valor = $dados[$campo];
                        if ($campo === 'dataInfracao' && !empty($valor)) {
                            $valor = date('d/m/Y', strtotime($valor));
                        }
                        echo "<tr><td width='30%'><strong>$rotulo:</strong></td>";
                        echo "<td>" . htmlspecialchars($valor) . "</td></tr>";
                    }
                }
                ?>
            </table>

            <div class="section-title">DADOS DO REQUERENTE</div>
            <table class="data-table">
                <?php
                $campos_requerente = [
                    'nome' => 'Nome',
                    'cpf' => 'CPF',
                    'telefone' => 'Telefone',
                    'email' => 'Email',
                    'endereco' => 'Endereço',
                    'numero' => 'Número',
                    'complemento' => 'Complemento',
                    'bairro' => 'Bairro',
                    'cep' => 'CEP',
                    'municipio' => 'Município'
                ];

                foreach ($campos_requerente as $campo => $rotulo) {
                    if (isset($dados[$campo]) && !strpos($campo, '_url')) {
                        echo "<tr><td width='30%'><strong>$rotulo:</strong></td>";
                        echo "<td>" . htmlspecialchars($dados[$campo]) . "</td></tr>";
                    }
                }

                if ($dados['tipo_solicitacao'] == 'apresentacao_condutor') {
                    $campos_condutor = [
                        'cnh' => 'CNH',
                        'uf' => 'UF',
                        'categoria' => 'Categoria'
                    ];

                    foreach ($campos_condutor as $campo => $rotulo) {
                        if (isset($dados[$campo])) {
                            echo "<tr><td width='30%'><strong>$rotulo:</strong></td>";
                            echo "<td>" . htmlspecialchars($dados[$campo]) . "</td></tr>";
                        }
                    }
                }
                ?>
            </table>

            <?php if (!empty($dados['defesa'])): ?>
                <div class="section-title">FUNDAMENTAÇÃO DA DEFESA</div>
                <div class="p-4" style="background-color: #f8f9fa; border-radius: 4px;">
                    <?php echo nl2br(htmlspecialchars($dados['defesa'])); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="signature-section">
            <div class="row">
                <div class="col-6 text-center">
                    <div class="signature-line"></div>
                    <p>Assinatura do Requerente</p>
                </div>
                <div class="col-6 text-center">
                    <div class="signature-line"></div>
                    <p>Assinatura do Servidor</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>