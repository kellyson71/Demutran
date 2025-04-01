<?php
include '../env/config.php';

// Aumentar limite de upload para 50MB
ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 300); // 5 minutos
ini_set('max_input_time', 300); // 5 minutos

// Definir o tamanho máximo de arquivo em bytes (50MB)
$max_file_size = 50 * 1024 * 1024;

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
    $error_message = null;

    // Verificar permissões de escrita no servidor
    $server_tmp_dir = sys_get_temp_dir();
    if (!is_writable($server_tmp_dir)) {
        error_log("Diretório temporário do servidor não tem permissão de escrita: " . $server_tmp_dir);
    }

    // Caminho absoluto do diretório de upload
    $abs_upload_dir = realpath(dirname(__FILE__) . '/../') . '/midia/cartao/';
    error_log("Caminho absoluto do diretório de upload: " . $abs_upload_dir);

    // Verificar se o arquivo foi enviado
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        $error_code = isset($_FILES[$file_key]) ? $_FILES[$file_key]['error'] : -1;

        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                $error_message = "O arquivo excede o tamanho máximo permitido pelo servidor.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = "O arquivo excede o tamanho máximo permitido pelo formulário.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = "O upload do arquivo foi interrompido.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = "Nenhum arquivo foi enviado.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message = "Diretório temporário não encontrado.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message = "Falha ao gravar arquivo no disco.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message = "Upload interrompido por extensão.";
                break;
            default:
                $error_message = "Erro desconhecido ao fazer upload.";
        }

        error_log("Erro ao fazer upload do arquivo '{$file_key}': {$error_message}");
        return ['url' => null, 'error' => $error_message];
    }

    // Obter extensão do arquivo original
    $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));

    // Verificar extensão permitida
    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowed_extensions)) {
        $error_message = "Extensão de arquivo não permitida. Apenas PDF, JPG, JPEG e PNG são aceitos.";
        error_log("Erro de extensão ({$ext}) para o arquivo '{$file_key}': {$error_message}");
        return ['url' => null, 'error' => $error_message];
    }

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

    // Criar diretório se não existir
    $dir_with_id = $upload_dir . $id_solicitacao . '/';
    if (!is_dir($dir_with_id)) {
        // Verificar permissões de diretório pai
        error_log("Tentando criar diretório: " . $dir_with_id);
        error_log("Diretório pai existe: " . (is_dir($upload_dir) ? "Sim" : "Não"));
        error_log("Permissões do diretório pai: " . substr(sprintf('%o', fileperms($upload_dir)), -4));

        if (!mkdir($dir_with_id, 0777, true)) {
            $error_message = "Erro ao criar diretório para os arquivos.";
            error_log("Falha ao criar diretório: {$dir_with_id}");
            return ['url' => null, 'error' => $error_message];
        } else {
            // Garantir permissões corretas
            chmod($dir_with_id, 0777);
            error_log("Diretório criado com sucesso: " . $dir_with_id);
        }
    }

    $target_path = $dir_with_id . $file_name;

    // Fazer upload do arquivo
    if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_path)) {
        // Verificar se o arquivo foi realmente criado
        if (file_exists($target_path)) {
            error_log("Arquivo '{$file_key}' salvo com sucesso em: {$target_path}");
            // Retornar URL relativa para o banco de dados
            return ['url' => '/midia/cartao/' . $id_solicitacao . '/' . $file_name, 'error' => null];
        } else {
            $error_message = "Arquivo foi movido mas não encontrado no destino.";
            error_log("Arquivo não encontrado após upload: {$target_path}");
            return ['url' => null, 'error' => $error_message];
        }
    } else {
        $error_message = "Falha ao mover o arquivo para o destino.";
        error_log("Falha ao mover arquivo para: {$target_path}");
        return ['url' => null, 'error' => $error_message];
    }
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

        // Configurar diretório de upload
        $base_path = realpath(dirname(__FILE__) . '/../');
        $upload_dir = $base_path . '/midia/cartao/';
        $base_url = $_SERVER['HTTP_HOST'];

        // Verificar e criar o diretório raiz se necessário
        $root_dir = $base_path . '/midia/';
        $cartao_dir = $base_path . '/midia/cartao/';

        error_log("Usando caminho base: " . $base_path);
        error_log("Diretório raiz: " . $root_dir);
        error_log("Diretório cartão: " . $cartao_dir);

        // Criar diretórios se não existirem
        if (!is_dir($root_dir)) {
            error_log("Criando diretório raiz: " . $root_dir);
            if (!mkdir($root_dir, 0777, true)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => ["Erro ao criar diretório base."]]);
                exit;
            }
            chmod($root_dir, 0777);
        }

        if (!is_dir($cartao_dir)) {
            error_log("Criando diretório de cartões: " . $cartao_dir);
            if (!mkdir($cartao_dir, 0777, true)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => ["Erro ao criar diretório de cartões."]]);
                exit;
            }
            chmod($cartao_dir, 0777);
        }

        // Array para armazenar erros de upload
        $upload_errors = [];

        // Verificar se os documentos obrigatórios foram enviados
        $documentos_obrigatorios = ['doc_identidade', 'comprovante_residencia'];
        if ($tipo_solicitacao === 'pcd') {
            $documentos_obrigatorios[] = 'laudo_medico';
        }

        // Verificar existência dos documentos obrigatórios
        foreach ($documentos_obrigatorios as $doc) {
            if (!isset($_FILES[$doc]) || $_FILES[$doc]['error'] === UPLOAD_ERR_NO_FILE) {
                $upload_errors[] = "O documento " . str_replace('_', ' ', $doc) . " é obrigatório.";
            }
        }

        if ($representante_legal) {
            if (!isset($_FILES['doc_identidade_representante']) || $_FILES['doc_identidade_representante']['error'] === UPLOAD_ERR_NO_FILE) {
                $upload_errors[] = "O documento de identidade do representante é obrigatório.";
            }
            if (!isset($_FILES['proc_comprovante']) || $_FILES['proc_comprovante']['error'] === UPLOAD_ERR_NO_FILE) {
                $upload_errors[] = "O documento de procuração é obrigatório.";
            }
        }

        // Se houver erros de documentos obrigatórios, não continuar com o upload
        if (!empty($upload_errors)) {
            // Remover o registro criado, pois faltam documentos
            $delete_sql = "DELETE FROM solicitacao_cartao WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param('i', $id_solicitacao);
            $delete_stmt->execute();

            // Retornar erro em formato JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => $upload_errors]);
            exit;
        }

        // Fazer upload dos arquivos
        $upload_doc_identidade = uploadFile('doc_identidade', $upload_dir, $base_url, $id_solicitacao);
        $upload_comprovante_residencia = uploadFile('comprovante_residencia', $upload_dir, $base_url, $id_solicitacao);
        $upload_laudo_medico = uploadFile('laudo_medico', $upload_dir, $base_url, $id_solicitacao);
        $upload_doc_identidade_representante = uploadFile('doc_identidade_representante', $upload_dir, $base_url, $id_solicitacao);
        $upload_proc_comprovante = uploadFile('proc_comprovante', $upload_dir, $base_url, $id_solicitacao);

        // Extrair URLs e verificar erros
        $doc_identidade_url = $upload_doc_identidade['url'];
        $comprovante_residencia_url = $upload_comprovante_residencia['url'];
        $laudo_medico_url = $upload_laudo_medico['url'];
        $doc_identidade_representante_url = $upload_doc_identidade_representante['url'];
        $proc_comprovante_url = $upload_proc_comprovante['url'];

        // Coletar erros
        if ($upload_doc_identidade['error']) $upload_errors[] = "Documento de identidade: " . $upload_doc_identidade['error'];
        if ($upload_comprovante_residencia['error']) $upload_errors[] = "Comprovante de residência: " . $upload_comprovante_residencia['error'];
        if ($tipo_solicitacao === 'pcd' && $upload_laudo_medico['error']) $upload_errors[] = "Laudo médico: " . $upload_laudo_medico['error'];
        if ($representante_legal) {
            if ($upload_doc_identidade_representante['error']) $upload_errors[] = "Documento do representante: " . $upload_doc_identidade_representante['error'];
            if ($upload_proc_comprovante['error']) $upload_errors[] = "Procuração: " . $upload_proc_comprovante['error'];
        }

        // Se houver erros de upload, remover o registro e retornar erro
        if (!empty($upload_errors)) {
            // Remover o registro criado, pois houve erro no upload
            $delete_sql = "DELETE FROM solicitacao_cartao WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param('i', $id_solicitacao);
            $delete_stmt->execute();

            // Retornar erro em formato JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => $upload_errors]);
            exit;
        }

        // Atualizar o registro com as URLs dos arquivos
        $update_sql = "UPDATE solicitacao_cartao SET 
            doc_identidade_url = ?,
            comprovante_residencia_url = ?,
            laudo_medico_url = ?,
            doc_identidade_representante_url = ?,
            proc_comprovante_url = ?
            WHERE id = ?";

        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param(
            'sssssi',
            $doc_identidade_url,
            $comprovante_residencia_url,
            $laudo_medico_url,
            $doc_identidade_representante_url,
            $proc_comprovante_url,
            $id_solicitacao
        );

        if (!$update_stmt->execute()) {
            // Se falhar em atualizar o banco de dados
            error_log("Erro ao atualizar URLs dos documentos: " . $update_stmt->error);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => ["Erro ao salvar os dados dos documentos no sistema."]]);
            exit;
        }

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

        // Enviar resposta de sucesso em JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Solicitação realizada com sucesso!',
            'id_solicitacao' => $id_solicitacao
        ]);
        exit;
    } else {
        // Erro ao executar a consulta principal
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'errors' => ["Erro ao processar a solicitação: " . $stmt->error]
        ]);
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>