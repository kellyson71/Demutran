<?php
include '../env/config.php';

// Aumentar limite de upload
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Função para verificar campos de texto e atribuir "não informado" se não receber valor
function verificaTexto($valor) {
    return isset($valor) && !empty($valor) ? $valor : "não informado";
}

// Função para fazer upload de arquivo (atualizada com nomes personalizados)
function uploadFile($file_key, $upload_dir, $base_url, $id_solicitacao) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        // Obter extensão do arquivo original
        $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));

        // Define o nome do arquivo baseado no tipo de documento
        $nomes_arquivos = [
            'doc_identidade' => 'rg',
            'comprovante_residencia' => 'comprovante_residencia',
            'laudo_medico' => 'laudo_medico',
            'doc_identidade_representante' => 'rg_representante',
            'proc_comprovante' => 'procuracao'
        ];

        // Pega o nome apropriado ou usa o file_key como fallback
        $novo_nome = isset($nomes_arquivos[$file_key]) ? $nomes_arquivos[$file_key] : $file_key;

        // Criar nome final do arquivo
        $file_name = $novo_nome . '.' . $ext;
        
        $dir_with_id = $upload_dir . $id_solicitacao . '/';
        if (!is_dir($dir_with_id)) {
            mkdir($dir_with_id, 0777, true);
        }
        $target_path = $dir_with_id . $file_name;
        if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_path)) {
            return 'https://' . $base_url . '/midia/cartao/' . $id_solicitacao . '/' . $file_name;
        }
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar campos obrigatórios
    $required_fields = [
        'tipo_solicitacao',
        'emissao_cartao',
        'solicitante',
        'nome',
        'data_nascimento',
        'cpf',
        'endereco',
        'telefone',
        'doc_identidade_num',
        'email'
    ];

    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        die("Campos obrigatórios faltando: " . implode(", ", $missing_fields));
    }

    // Captura os dados do formulário
    $residente = isset($_POST['resident_check']) ? 1 : 0;
    $tipo_solicitacao = $_POST['tipo_solicitacao'];
    $emissao_cartao = $_POST['emissao_cartao'];
    $solicitante = $_POST['solicitante'];
    $nome = $_POST['nome'];
    $data_nascimento = $_POST['data_nascimento'];
    $cpf = $_POST['cpf'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $doc_identidade_num = $_POST['doc_identidade_num'];
    $email = $_POST['email'];

    // Inicialmente, os URLs de arquivo serão nulos
    $doc_identidade_url = null;
    $comprovante_residencia_url = null;
    $laudo_medico_url = null;
    $doc_identidade_representante_url = null;
    $proc_comprovante_url = null;

    $representante_legal = isset($_POST['representante_legal_check']) ? 1 : 0;

    if ($representante_legal) {
        $nome_representante = verificaTexto($_POST['nome_representante']);
        $cpf_representante = verificaTexto($_POST['cpf_representante']);
        $endereco_representante = verificaTexto($_POST['endereco_representante']);
        $telefone_representante = verificaTexto($_POST['telefone_representante']);
        $email_representante = verificaTexto($_POST['email_representante']);
    } else {
        $nome_representante = $cpf_representante = $endereco_representante = $telefone_representante = $email_representante = "não informado";
    }

    // Verificar se a tabela contadores_cartao existe
    $check_table = "SHOW TABLES LIKE 'contadores_cartao'";
    $table_exists = $conn->query($check_table)->num_rows > 0;

    if (!$table_exists) {
        // Criar tabela se não existir
        $create_table = "CREATE TABLE IF NOT EXISTS contadores_cartao (
            tipo VARCHAR(10) PRIMARY KEY,
            ultimo_numero INT NOT NULL DEFAULT 0
        )";
        $conn->query($create_table);

        // Inserir valores iniciais
        $insert_initial = "INSERT INTO contadores_cartao (tipo, ultimo_numero) VALUES 
            ('pcd', 0),
            ('idoso', 0)";
        $conn->query($insert_initial);
    }

    // Preparar a query de inserção
    $sql = "INSERT INTO solicitacao_cartao (
        residente, tipo_solicitacao, emissao_cartao, solicitante, nome, data_nascimento, cpf, endereco, telefone,
        doc_identidade_num, email, doc_identidade_url, comprovante_residencia_url, laudo_medico_url,
        representante_legal, nome_representante, cpf_representante, endereco_representante, telefone_representante,
        email_representante, doc_identidade_representante_url, proc_comprovante_url
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // Verifica se a preparação falhou
    if (!$stmt) {
        die("Erro na preparação da declaração: " . $conn->error);
    }

    // Como inicialmente os URLs de arquivos são nulos, passamos null para eles
    $stmt->bind_param(
        "isssssssssssssisssssss",
        $residente,
        $tipo_solicitacao,
        $emissao_cartao,
        $solicitante,
        $nome,
        $data_nascimento,
        $cpf,
        $endereco,
        $telefone,
        $doc_identidade_num,
        $email,
        $doc_identidade_url, // null
        $comprovante_residencia_url, // null
        $laudo_medico_url, // null
        $representante_legal,
        $nome_representante,
        $cpf_representante,
        $endereco_representante,
        $telefone_representante,
        $email_representante,
        $doc_identidade_representante_url, // null
        $proc_comprovante_url // null
    );

    if ($stmt->execute()) {
        $id_solicitacao = $conn->insert_id;

        // Obter o número atual e incrementar
        $get_numero = "SELECT ultimo_numero FROM contadores_cartao WHERE tipo = ?";
        $stmt_numero = $conn->prepare($get_numero);
        if (!$stmt_numero) {
            die("Erro na preparação do número: " . $conn->error);
        }

        $stmt_numero->bind_param('s', $tipo_solicitacao);
        $stmt_numero->execute();
        $result = $stmt_numero->get_result();

        if ($row = $result->fetch_assoc()) {
            $proximo_numero = $row['ultimo_numero'] + 1;

            // Formatar o número do cartão
            $prefixo = strtoupper(substr($tipo_solicitacao, 0, 1)); // 'P' para PCD ou 'I' para Idoso
            $n_cartao = $prefixo . '2025' . str_pad($proximo_numero, 3, '0', STR_PAD_LEFT);

            // Atualizar o contador
            $update_contador = "UPDATE contadores_cartao SET ultimo_numero = ?, data_atualizacao = CURRENT_TIMESTAMP WHERE tipo = ?";
            $stmt_contador = $conn->prepare($update_contador);
            $stmt_contador->bind_param('is', $proximo_numero, $tipo_solicitacao);
            $stmt_contador->execute();

            // Atualizar o registro com o número do cartão
            $update_cartao = "UPDATE solicitacao_cartao SET n_cartao = ? WHERE id = ?";
            $stmt_cartao = $conn->prepare($update_cartao);
            $stmt_cartao->bind_param('si', $n_cartao, $id_solicitacao);
            $stmt_cartao->execute();
        }

        // Salvar os valores originais do POST
        $original_post = $_POST;

        // Configurar os dados para envio de email
        $_POST = array(
            'email' => $email, // Usando o email do formulário
            'nome' => $nome,
            'assunto' => "Solicitação de Cartão Vaga Especial - Protocolo #{$id_solicitacao}",
            'mensagem' => "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='background-color: #f5f5f5; padding: 20px;'>
                    <h2 style='color: #2c5282;'>Solicitação Recebida</h2>
                    <p>Prezado(a) {$nome},</p>
                    <p>Sua solicitação de Cartão Vaga Especial foi recebida com sucesso!</p>
                    <p><strong>Número de Protocolo:</strong> #{$id_solicitacao}</p>
                    <hr style='border: 1px solid #e2e8f0;'>
                    <p><strong>Próximos Passos:</strong></p>
                    <ol style='margin-left: 20px;'>
                        <li>Sua solicitação será analisada pela nossa equipe</li>
                        <li>Você receberá as atualizações sobre sua solicitação neste e-mail</li>
                        <li>O prazo para análise é de até 15 dias úteis</li>
                        <li>Se aprovada, você receberá instruções para retirada do cartão</li>
                        <li>Se houver pendências, você será notificado para regularização</li>
                    </ol>
                    <p><strong>IMPORTANTE:</strong> Este é um e-mail automático, não responda.</p>
                    <div style='background-color: #ffffff; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <p><strong>Canais de Atendimento DEMUTRAN:</strong></p>
                        <p>📞 Telefone: (84) 3351-2868</p>
                        <p>📧 E-mail: demutran@paudosferros.rn.gov.br</p>
                        <p>📍 Endereço: Av. Getúlio Vargas, 1323, Centro, Pau dos Ferros-RN</p>
                    </div>
                </div>
            </body>
            </html>"
        );

        // Incluir e executar o envio de email
        try {
            require_once '../utils/mail.php';
            error_log("Enviando email para: " . $email);
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
        }

        // Restaurar os valores originais do POST
        $_POST = $original_post;

        // Diretório base para upload
        $upload_dir = '../midia/cartao/';
        // Não precisa mais definir $base_url aqui pois já vem do config.php

        // Criar pasta com o ID da solicitação
        $dir_with_id = $upload_dir . $id_solicitacao . '/';
        if (!is_dir($dir_with_id)) {
            mkdir($dir_with_id, 0777, true);
        }

        // Agora processar os arquivos e salvar nas novas pastas
        $doc_identidade_url = uploadFile('doc_identidade', $upload_dir, $base_url, $id_solicitacao);
        $comprovante_residencia_url = uploadFile('comprovante_residencia', $upload_dir, $base_url, $id_solicitacao);

        if ($tipo_solicitacao === 'pcd') {
            $laudo_medico_url = uploadFile('laudo_medico', $upload_dir, $base_url, $id_solicitacao);
        }

        if ($representante_legal) {
            $doc_identidade_representante_url = uploadFile('doc_identidade_representante', $upload_dir, $base_url, $id_solicitacao);
            $proc_comprovante_url = uploadFile('proc_comprovante', $upload_dir, $base_url, $id_solicitacao);
        }

        // Atualizar os campos de URLs no banco de dados
        $update_sql = "UPDATE solicitacao_cartao SET 
            doc_identidade_url = ?, 
            comprovante_residencia_url = ?, 
            laudo_medico_url = ?, 
            doc_identidade_representante_url = ?, 
            proc_comprovante_url = ?
            WHERE id = ?";

        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            die("Erro na preparação da atualização: " . $conn->error);
        }

        $update_stmt->bind_param(
            "sssssi",
            $doc_identidade_url,
            $comprovante_residencia_url,
            $laudo_medico_url,
            $doc_identidade_representante_url,
            $proc_comprovante_url,
            $id_solicitacao
        );

        if ($update_stmt->execute()) {
            echo "Dados inseridos com sucesso!";
        } else {
            echo "Erro ao atualizar os arquivos: " . $update_stmt->error;
        }

        $update_stmt->close();
    } else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>