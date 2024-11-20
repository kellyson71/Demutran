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

// Função para fazer upload de arquivo
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

// Função para fazer upload de múltiplos arquivos
function uploadMultipleFiles($file_key, $upload_dir, $base_url, $id_solicitacao) {
    $urls = [];
    if (isset($_FILES[$file_key])) {
        for ($i = 0; $i < count($_FILES[$file_key]['name']); $i++) {
            if ($_FILES[$file_key]['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = basename($_FILES[$file_key]['name'][$i]);
                $file_name = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $file_name);

                $dir_with_id = $upload_dir . $id_solicitacao . '/';
                if (!is_dir($dir_with_id)) {
                    mkdir($dir_with_id, 0777, true);
                }
                $target_path = $dir_with_id . $file_name;
                if (move_uploaded_file($_FILES[$file_key]['tmp_name'][$i], $target_path)) {
                    $urls[] = $base_url . $target_path;
                }
            }
        }
    }
    return implode(';', $urls); // Armazena as URLs separadas por ponto e vírgula
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura os dados do formulário
    $nome = verificaTexto($_POST['nome']);
    $telefone = verificaTexto($_POST['telefone']);
    $email = verificaTexto($_POST['email']);
    $assunto = verificaTexto($_POST['assunto']);

    // Inicialmente, os URLs de arquivo serão nulos
    $doc_requerimento_url = null;
    $cnh_url = null;
    $notif_DEMUTRAN_url = null;
    $crlv_url = null;
    $comprovante_residencia_url = null;
    $doc_complementares_urls = null;

    // Preparar a query de inserção
    $sql = "INSERT INTO defesa_previa (
        nome, telefone, email, assunto, doc_requerimento_url, cnh_url, notif_DEMUTRAN_url, crlv_url, comprovante_residencia_url, doc_complementares_urls
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // Verifica se a preparação falhou
    if (!$stmt) {
        die("Erro na preparação da declaração: " . $conn->error);
    }

    // Como inicialmente os URLs de arquivos são nulos, passamos null para eles
    $stmt->bind_param(
        "ssssssssss",
        $nome,
        $telefone,
        $email,
        $assunto,
        $doc_requerimento_url,
        $cnh_url,
        $notif_DEMUTRAN_url,
        $crlv_url,
        $comprovante_residencia_url,
        $doc_complementares_urls
    );

    if ($stmt->execute()) {
        // Obter o ID inserido
        $id_solicitacao = $conn->insert_id;

        // Diretório base para upload
        $upload_dir = 'midia/';
        $base_url = "https://seusite.com/midia/"; // Atualize com a URL base correta

        // Criar pasta com o ID da solicitação
        $dir_with_id = $upload_dir . $id_solicitacao . '/';
        if (!is_dir($dir_with_id)) {
            mkdir($dir_with_id, 0777, true);
        }

        // Agora processar os arquivos e salvar nas novas pastas
        $doc_requerimento_url = uploadFile('doc_requerimento', $upload_dir, $base_url, $id_solicitacao);
        $cnh_url = uploadFile('cnh', $upload_dir, $base_url, $id_solicitacao);
        $notif_DEMUTRAN_url = uploadFile('notif_DEMUTRAN', $upload_dir, $base_url, $id_solicitacao);
        $crlv_url = uploadFile('crlv', $upload_dir, $base_url, $id_solicitacao);
        $comprovante_residencia_url = uploadFile('comprovante_residencia', $upload_dir, $base_url, $id_solicitacao);
        $doc_complementares_urls = uploadMultipleFiles('doc_complementares', $upload_dir, $base_url, $id_solicitacao);

        // Atualizar os campos de URLs no banco de dados
        $update_sql = "UPDATE defesa_previa SET 
            doc_requerimento_url = ?, 
            cnh_url = ?, 
            notif_DEMUTRAN_url = ?, 
            crlv_url = ?, 
            comprovante_residencia_url = ?,
            doc_complementares_urls = ?
            WHERE id = ?";

        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            die("Erro na preparação da atualização: " . $conn->error);
        }

        $update_stmt->bind_param(
            "ssssssi",
            $doc_requerimento_url,
            $cnh_url,
            $notif_DEMUTRAN_url,
            $crlv_url,
            $comprovante_residencia_url,
            $doc_complementares_urls,
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