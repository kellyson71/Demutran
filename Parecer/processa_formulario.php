<?php
session_start();
include '../env/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validar dados básicos
        $requiredFields = ['nome_solicitante', 'telefone', 'cpf_cnpj', 'email', 'local', 'evento', 'ponto_referencia', 'data_evento', 'horario_inicio', 'horario_fim'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Campo $field é obrigatório");
            }
        }

        // Modificar a conversão da data
        $data_evento = DateTime::createFromFormat('d/m/Y', $_POST['data_evento']);
        if (!$data_evento) {
            throw new Exception("Formato de data inválido");
        }
        $data_formatada = $data_evento->format('d/m/Y');
        $data_horario = $data_formatada . " " . $_POST['horario_inicio'] . " às " . $_POST['horario_fim'];

        // Gerar protocolo
        $protocolo = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        // Inserir dados básicos
        $stmt = $conn->prepare("INSERT INTO Parecer (protocolo, nome, telefone, cpf_cnpj, email, local, evento, 
                               ponto_referencia, data_horario, declaracao) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $declaracao = isset($_POST['declaracao']) ? 1 : 0;
        $stmt->bind_param("sssssssssi", 
            $protocolo,
            $_POST['nome_solicitante'],
            $_POST['telefone'],
            $_POST['cpf_cnpj'],
            $_POST['email'],
            $_POST['local'],
            $_POST['evento'],
            $_POST['ponto_referencia'],
            $data_horario,
            $declaracao
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao salvar dados: " . $stmt->error);
        }

        $lastId = $conn->insert_id;

        // Nova estrutura de diretório para uploads
        $uploadDir = '../midia/parecer/' . $lastId . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Mapeamento de inputs para nomes de arquivo
        $arquivos = [
            'doc_identificacao' => 'documento_identificacao',
            'comp_residencia' => 'comprovante_residencia'
        ];

        $uploadedFiles = [];
        foreach ($arquivos as $input => $prefix) {
            if (!isset($_FILES[$input]) || $_FILES[$input]['error'] != 0) {
                throw new Exception("Erro no upload do arquivo $input");
            }

            $file = $_FILES[$input];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Validações
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                throw new Exception("Tipo de arquivo inválido para $input");
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("Arquivo $input muito grande");
            }

            $fileName = $prefix . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception("Erro ao mover arquivo $input");
            }

            // Construir URL completa para o banco de dados
            $fileUrl = "https://{$base_url}/midia/parecer/{$lastId}/{$fileName}";
            $uploadedFiles[$input] = $fileUrl;
        }

        // Atualizar registro com URLs completas dos arquivos
        $stmt = $conn->prepare("UPDATE Parecer SET documento_identificacao = ?, comprovante_residencia = ? WHERE id = ?");
        $stmt->bind_param("ssi", 
            $uploadedFiles['doc_identificacao'],
            $uploadedFiles['comp_residencia'],
            $lastId
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar arquivos: " . $stmt->error);
        }

        if ($stmt->execute()) {
            // Configurar os dados para envio de email
            $_POST = array(
                'email' => $_POST['email'],
                'nome' => $_POST['nome_solicitante'],
                'assunto' => "Solicitação de Parecer DEMUTRAN - Protocolo #{$protocolo}",
                'mensagem' => "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <div style='background-color: #f5f5f5; padding: 20px;'>
                        <h2 style='color: #2c5282;'>Solicitação de Parecer Recebida</h2>
                        <p>Prezado(a) {$_POST['nome_solicitante']},</p>
                        <p>Sua solicitação de parecer foi recebida com sucesso!</p>
                        <p><strong>Número de Protocolo:</strong> #{$protocolo}</p>
                        <hr style='border: 1px solid #e2e8f0;'>
                        <p><strong>Detalhes da Solicitação:</strong></p>
                        <ul style='margin-left: 20px;'>
                            <li>Local: {$_POST['local']}</li>
                            <li>Evento: {$_POST['evento']}</li>
                            <li>Data/Horário: {$data_horario}</li>
                        </ul>
                        <p><strong>Próximos Passos:</strong></p>
                        <ol style='margin-left: 20px;'>
                            <li>Sua solicitação será analisada pela equipe técnica do DEMUTRAN</li>
                            <li>O prazo para emissão do parecer é de até 4 dias úteis</li>
                            <li>Você receberá um e-mail quando o parecer estiver pronto para retirada</li>
                            <li>O documento deverá ser retirado presencialmente na sede do DEMUTRAN</li>
                            <li>Lembre-se de apresentar um documento de identificação no momento da retirada</li>
                        </ol>
                        <p><strong>IMPORTANTE:</strong></p>
                        <ul style='margin-left: 20px; color: #e53e3e;'>
                            <li>Este é um e-mail automático, não responda</li>
                            <li>O parecer NÃO é enviado por e-mail, apenas presencialmente</li>
                            <li>Guarde seu número de protocolo para consultas futuras</li>
                        </ul>
                        <div style='background-color: #ffffff; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                            <p><strong>Canais de Atendimento DEMUTRAN:</strong></p>
                            <p>📞 Telefone: (84) 3351-2868</p>
                            <p>📧 E-mail: demutran@paudosferros.rn.gov.br</p>
                            <p>📍 Endereço: Av. Getúlio Vargas, 1323, Centro, Pau dos Ferros-RN</p>
                            <p>⏰ Horário de Atendimento: Segunda a Sexta, das 07h às 13h</p>
                        </div>
                    </div>
                </body>
                </html>"
            );

            // Incluir e executar o envio de email
            require_once '../utils/mail.php';
        }

        echo json_encode(['success' => true, 'message' => "Solicitação enviada com sucesso! Seu protocolo é: $protocolo"]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }

    // Fechar conexões
    if (isset($stmt)) $stmt->close();
    $conn->close();
}

// Se não for POST, retornar erro
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}