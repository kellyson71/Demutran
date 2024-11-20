<?php

// Enable error reporting at the beginning of the script
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "srv1078.hstgr.io"; 
$username = "u492577848_protocolo";
$password = "WRVGAxCbrJ8wdM$"; 
$dbname = "u492577848_demutran";

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
            return $base_url . $dir_with_id . $file_name;
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
                    $urls[] = $base_url . $dir_with_id . $file_name;
                }
            }
        }
    }
    return implode(';', $urls); // Armazena as URLs separadas por ponto e vírgula
}

// Function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

// Ensure necessary columns exist
$columns = [
    'doc_requerimento_url',
    'cnh_url',
    'cnh_condutor_url',
    'notif_DEMUTRAN_url',
    'crlv_url',
    'comprovante_residencia_url',
    'doc_complementares_urls',
    'signed_document_url' // New column for the signed document URL
];

foreach ($columns as $column) {
    if (!columnExists($conn, 'solicitacoes_demutran', $column)) {
        $conn->query("ALTER TABLE solicitacoes_demutran ADD COLUMN `$column` VARCHAR(255) DEFAULT NULL");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug: Log received POST data and uploaded files
    file_put_contents('debug_log.txt', "POST DATA:\n" . print_r($_POST, true), FILE_APPEND);
    file_put_contents('debug_log.txt', "FILES DATA:\n" . print_r($_FILES, true), FILE_APPEND);

    // Make sure required fields are present
    if (empty($_POST['tipoRequerente'])) {
        die("Tipo de solicitação não informado.");
    }

    // Captura o tipo de solicitação
    $tipo_solicitacao = verificaTexto($_POST['tipoRequerente']);

    // Diretório base para upload
    $upload_dir = 'midia/';
    $base_url = "https://seusite.com/Defesa/midia/"; // Atualize com a URL base correta

    // Inicialmente, os URLs de arquivo serão nulos
    $doc_requerimento_url = null;
    $cnh_url = null;
    $cnh_condutor_url = null;
    $notif_DEMUTRAN_url = null;
    $crlv_url = null;
    $comprovante_residencia_url = null;
    $doc_complementares_urls = null;
    $signed_document_url = null;
    $descricao = null;

    // Captura os dados do formulário
    $nome = verificaTexto($_POST['nome']);
    $cpf = verificaTexto($_POST['cpf']);
    $endereco = verificaTexto($_POST['endereco']);
    $numero = verificaTexto($_POST['numero']);
    $complemento = verificaTexto($_POST['complemento']);
    $bairro = verificaTexto($_POST['bairro']);
    $cep = verificaTexto($_POST['cep']);
    $municipio = verificaTexto($_POST['municipio']);
    $telefone = verificaTexto($_POST['telefone']);
    $placa = verificaTexto($_POST['placa']);
    $marcaModelo = verificaTexto($_POST['marcaModelo']);
    $cor = verificaTexto($_POST['cor']);
    $especie = verificaTexto($_POST['especie']);
    $categoria = verificaTexto($_POST['categoria']);
    $ano = verificaTexto($_POST['ano']);
    $autoInfracao = verificaTexto($_POST['autoInfracao']);
    $dataInfracao = verificaTexto($_POST['dataInfracao']);
    $horaInfracao = verificaTexto($_POST['horaInfracao']);
    $localInfracao = verificaTexto($_POST['localInfracao']);
    $enquadramento = verificaTexto($_POST['enquadramento']);
    $defesa = verificaTexto($_POST['defesa']);

    // Inserir registro com os dados
    $sql = "INSERT INTO solicitacoes_demutran (
        tipo_solicitacao, nome, cpf, endereco, numero, complemento, bairro, cep, municipio, telefone, placa, marcaModelo, cor, especie, categoria, ano, autoInfracao, dataInfracao, horaInfracao, localInfracao, enquadramento, defesa,
        doc_requerimento_url, cnh_url, cnh_condutor_url, notif_DEMUTRAN_url, crlv_url, comprovante_residencia_url, doc_complementares_urls, signed_document_url
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro na preparação da declaração: " . $conn->error);
    }

    // Inserimos valores temporários; as URLs serão atualizadas após o upload
    $stmt->bind_param(
        "ssssssssssssssssssssssssssssss",
        $tipo_solicitacao,
        $nome,
        $cpf,
        $endereco,
        $numero,
        $complemento,
        $bairro,
        $cep,
        $municipio,
        $telefone,
        $placa,
        $marcaModelo,
        $cor,
        $especie,
        $categoria,
        $ano,
        $autoInfracao,
        $dataInfracao,
        $horaInfracao,
        $localInfracao,
        $enquadramento,
        $defesa,
        $doc_requerimento_url,
        $cnh_url,
        $cnh_condutor_url,
        $notif_DEMUTRAN_url,
        $crlv_url,
        $comprovante_residencia_url,
        $doc_complementares_urls,
        $signed_document_url
    );

    if ($stmt->execute()) {
        // Obter o ID inserido
        $id_solicitacao = $conn->insert_id;

        // Criar pasta com o ID da solicitação
        $dir_with_id = $upload_dir . $id_solicitacao . '/';
        if (!is_dir($dir_with_id)) {
            mkdir($dir_with_id, 0777, true);
        }

        // Agora processar os arquivos e salvar nas novas pastas
        $doc_requerimento_url = uploadFile('doc_requerimento', $upload_dir, $base_url, $id_solicitacao);
        $cnh_url = uploadFile('cnh', $upload_dir, $base_url, $id_solicitacao);
        $cnh_condutor_url = uploadFile('cnh_condutor', $upload_dir, $base_url, $id_solicitacao);
        $notif_DEMUTRAN_url = uploadFile('notif_DEMUTRAN', $upload_dir, $base_url, $id_solicitacao);
        $crlv_url = uploadFile('crlv', $upload_dir, $base_url, $id_solicitacao);
        $comprovante_residencia_url = uploadFile('comprovante_residencia', $upload_dir, $base_url, $id_solicitacao);
        $doc_complementares_urls = uploadMultipleFiles('doc_complementares', $upload_dir, $base_url, $id_solicitacao);

        // Handle file upload for signed document
        if (isset($_FILES['signedDocument']) && $_FILES['signedDocument']['error'] === UPLOAD_ERR_OK) {
            $file_name = basename($_FILES['signedDocument']['name']);
            $file_name = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $file_name);
            $target_path = $dir_with_id . $file_name;
            if (move_uploaded_file($_FILES['signedDocument']['tmp_name'], $target_path)) {
                $signed_document_url = $base_url . $id_solicitacao . '/' . $file_name;
            } else {
                echo "Erro ao enviar o arquivo.";
            }
        } else {
            echo "Nenhum arquivo enviado ou erro no upload.";
        }

        // Atualizar os campos de URLs no banco de dados
        $update_sql = "UPDATE solicitacoes_demutran SET 
            doc_requerimento_url = ?, 
            cnh_url = ?, 
            cnh_condutor_url = ?,
            notif_DEMUTRAN_url = ?, 
            crlv_url = ?, 
            comprovante_residencia_url = ?,
            doc_complementares_urls = ?,
            signed_document_url = ?
            WHERE id = ?";

        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            die("Erro na preparação da atualização: " . $conn->error);
        }

        $update_stmt->bind_param(
            "ssssssssi",
            $doc_requerimento_url,
            $cnh_url,
            $cnh_condutor_url,
            $notif_DEMUTRAN_url,
            $crlv_url,
            $comprovante_residencia_url,
            $doc_complementares_urls,
            $signed_document_url,
            $id_solicitacao
        );

        if ($update_stmt->execute()) {
            echo "Dados inseridos com sucesso!";
        } else {
            echo "Erro ao atualizar os arquivos: " . $update_stmt->error;
        }

        $update_stmt->close();
    } else {
        echo "Erro ao inserir os dados: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>