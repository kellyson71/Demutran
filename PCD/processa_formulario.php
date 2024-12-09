<?php
include '../env/config.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Função para verificar campos de texto e atribuir "não informado" se não receber valor
function verificaTexto($valor) {
    return isset($valor) && !empty($valor) ? $valor : "não informado";
}

// Função para fazer upload de arquivo (será atualizada)
function uploadFile($file_key, $upload_dir, $base_url, $id_solicitacao) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        // Sanitizar o nome do arquivo para evitar problemas de segurança
        $file_name = basename($_FILES[$file_key]['name']);
        $file_name = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $file_name);
        
        // Criar o diretório com o ID da solicitação se ainda não existir
        $dir_with_id = $upload_dir . $id_solicitacao . '/';
        if (!is_dir($dir_with_id)) {
            mkdir($dir_with_id, 0777, true);
        }
        $target_path = $dir_with_id . $file_name;
        if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_path)) {
            return $base_url . $target_path;
        }
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura os dados do formulário
    $residente = isset($_POST['resident_check']) ? 1 : 0;
    $tipo_solicitacao = verificaTexto($_POST['tipo_solicitacao']);
    $emissao_cartao = verificaTexto($_POST['emissao_cartao']);
    $solicitante = verificaTexto($_POST['solicitante']);
    $nome = verificaTexto($_POST['nome']);
    $data_nascimento = verificaTexto($_POST['data_nascimento']);
    $cpf = verificaTexto($_POST['cpf']);
    $endereco = verificaTexto($_POST['endereco']);
    $telefone = verificaTexto($_POST['telefone']);
    $doc_identidade_num = verificaTexto($_POST['doc_identidade_num']);
    $email = verificaTexto($_POST['email']);

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
        $upload_dir = 'midia/';
        $base_url = "https://seusite.com/midia/"; // Atualize com a URL base correta

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