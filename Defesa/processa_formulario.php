<?php
include '../env/config.php';

// Enable error reporting and increase memory limit
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M');

header('Content-Type: application/json');

// Debug logging
error_log("Raw POST data: " . file_get_contents("php://input"));
error_log("POST array: " . print_r($_POST, true));
error_log("FILES array: " . print_r($_FILES, true));

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode([
        'error' => true,
        'message' => "Conexão falhou: " . $conn->connect_error
    ]));
}

// Get tipo_solicitacao from POST data
$tipo_solicitacao = null;
if (isset($_POST['tipo_solicitacao'])) {
    $tipo_solicitacao = trim($_POST['tipo_solicitacao']);
} else {
    // Try to get from raw input if not in $_POST
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['tipo_solicitacao'])) {
        $tipo_solicitacao = trim($input['tipo_solicitacao']);
    }
}

if (empty($tipo_solicitacao)) {
    http_response_code(400);
    die(json_encode([
        'error' => true,
        'message' => 'Tipo de solicitação não informado.',
        'debug' => [
            'post' => $_POST,
            'raw' => file_get_contents("php://input")
        ]
    ]));
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
    // Captura o tipo de solicitação
    $tipo_solicitacao = isset($_POST['tipo_solicitacao']) ? $_POST['tipo_solicitacao'] : null;

    // Verifica se está vazio
    if (empty($tipo_solicitacao)) {
        die("Tipo de solicitação não informado.");
    }

    // Se chegou aqui, temos um tipo de solicitação válido
    $tipo_solicitacao = verificaTexto($tipo_solicitacao);

    // Diretório base para upload
    $upload_dir = 'midia/';
    $base_url = "https://seusite.com/Defesa/midia/"; // Atualize com a URL base correta

    // Inicialmente, os URLs de arquivo serão nulos
    $doc_requerimento_url = null;
    $cnh_url = null;
    $cnh_condutor_url = null;
    $notif_DEMUTRAN_url = null;
    $crlv_url = null;
    $comprovante_residencia_url = null;+
    $signed_document_url = null;
    $descricao = null;

    // Captura os emails
    $gmail = isset($_POST['gmail']) ? $_POST['gmail'] : null;
    $confirm_gmail = isset($_POST['confirm_gmail']) ? $_POST['confirm_gmail'] : null;

    // Verificar se ambos os campos foram preenchidos
    if (empty($gmail) || empty($confirm_gmail)) {
        die("Por favor, preencha ambos os campos de email.");
    }

    // Verificar se os emails são iguais
    if ($gmail !== $confirm_gmail) {
        die("Os emails informados não são iguais.");
    }

    // Sanitizar o email
    $gmail = verificaTexto($gmail);

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
        doc_requerimento_url, cnh_url, cnh_condutor_url, notif_DEMUTRAN_url, crlv_url, comprovante_residencia_url, doc_complementares_urls, signed_document_url, gmail
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro na preparação da declaração: " . $conn->error);
    }

    // Inserimos valores temporários; as URLs serão atualizadas após o upload
    $stmt->bind_param(
        "sssssssssssssssssssssssssssssss",
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
        $signed_document_url,
        $gmail
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

        // Adicione ao trecho onde são capturados os dados do formulário
        if ($tipo_solicitacao === 'apresentacao_condutor') {
            $identidade = verificaTexto($_POST['identidade']);
            
            // Adicione os campos de assinatura
            $assinatura_condutor_url = uploadFile('assinatura_condutor', $upload_dir, $base_url, $id_solicitacao);
            $assinatura_proprietario_url = uploadFile('assinatura_proprietario', $upload_dir, $base_url, $id_solicitacao);
            
            // Modifique a query SQL removendo o orgao_emissor
            $sql = "UPDATE solicitacoes_demutran SET 
                    identidade = ?,
                    assinatura_condutor_url = ?,
                    assinatura_proprietario_url = ?
                    WHERE id = ?";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", 
                $identidade,
                $assinatura_condutor_url,
                $assinatura_proprietario_url,
                $id_solicitacao
            );
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
            // Preservar os valores POST originais
            $original_post = $_POST;

            // Configurar os dados específicos para o envio de email
            $_POST = array(
                'email' => $gmail,
                'nome' => $nome,
                'assunto' => "Confirmação de Defesa/JARI - Protocolo #{$id_solicitacao}",
                'mensagem' => "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                // ...resto do HTML...
                </body>
                </html>"
            );

            // Incluir e executar o envio de email
            require_once '../utils/mail.php';

            // Restaurar os valores POST originais
            $_POST = $original_post;

            // Retornar mensagem de sucesso com codificação correta
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => 'Dados inseridos com sucesso! Um email de confirmação foi enviado.'
            ], JSON_UNESCAPED_UNICODE);
            
            // Dentro do if ($update_stmt->execute())
            $to = $gmail;
            $subject = "Confirmação de Defesa/JARI - Protocolo #{$id_solicitacao}";
            $message = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='background-color: #f5f5f5; padding: 20px;'>
                    <h2 style='color: #2c5282;'>Solicitação de {$tipo_solicitacao} Recebida</h2>
                    <p>Prezado(a) {$nome},</p>
                    <p>Sua solicitação foi recebida com sucesso!</p>
                    <p><strong>Número da Solicitação:</strong> #{$id_solicitacao}</p>
                    <hr style='border: 1px solid #e2e8f0;'>
                    <p><strong>Próximos Passos:</strong></p>
                    <ol style='margin-left: 20px;'>
                        <li>Sua defesa será analisada pela JARI (Junta Administrativa de Recursos de Infrações)</li>
                        <li>O prazo médio de análise é de 30 dias úteis</li>
                        <li>O resultado será enviado para este e-mail</li>
                        <li>Você poderá acompanhar o processo através do protocolo informado acima</li>
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
            </html>";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n";
            $headers .= "From: DEMUTRAN <demutran@paudosferros.rn.gov.br>\r\n";

            mail($to, $subject, $message, $headers);
        } else {
            echo json_encode([
                'error' => true,
                'message' => "Erro ao atualizar os arquivos: " . $update_stmt->error
            ]);
        }

        $update_stmt->close();
    } else {
        echo "Erro ao inserir os dados: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>