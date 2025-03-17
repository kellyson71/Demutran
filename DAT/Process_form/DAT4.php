<?php
require_once(__DIR__ . '/../../env/config.php');
header('Content-Type: application/json');

// Função para validar texto
function verificaTexto($valor)
{
    return isset($valor) && !empty($valor) ? $valor : "não informado";
}

try {
    // Verificar método da requisição
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido. Use POST para enviar dados.', 405);
    }

    // Verificar conexão com o banco
    if ($conn->connect_error) {
        throw new Exception('Erro de conexão com o banco de dados: ' . $conn->connect_error, 500);
    }

    // Validar token
    if (!isset($_POST['token']) || empty($_POST['token'])) {
        throw new Exception('Token não fornecido', 400);
    }

    $token = $_POST['token'];

    // Verificar se o token existe em formularios_dat_central
    $checkToken = $conn->prepare("SELECT id, preenchimento_status FROM formularios_dat_central WHERE token = ?");
    if (!$checkToken) {
        throw new Exception('Erro ao preparar consulta: ' . $conn->error, 500);
    }

    $checkToken->bind_param("s", $token);
    if (!$checkToken->execute()) {
        throw new Exception('Erro ao executar consulta: ' . $checkToken->error, 500);
    }

    $result = $checkToken->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Token inválido ou formulário não encontrado', 404);
    }

    $formularioInfo = $result->fetch_assoc();
    $checkToken->close();

    // Verificar se o formulário já foi completado
    if ($formularioInfo['preenchimento_status'] === 'completo') {
        throw new Exception('Este formulário já foi finalizado', 409);
    }

    // Verificar se o registro já existe
    $checkExisting = $conn->prepare("SELECT id FROM DAT4 WHERE token = ?");
    if (!$checkExisting) {
        throw new Exception('Erro ao preparar consulta: ' . $conn->error, 500);
    }

    $checkExisting->bind_param("s", $token);
    if (!$checkExisting->execute()) {
        throw new Exception('Erro ao executar consulta: ' . $checkExisting->error, 500);
    }

    $existingResult = $checkExisting->get_result();
    $isUpdate = $existingResult->num_rows > 0;
    $checkExisting->close();

    // Nomes das colunas da tabela
    $colunas = [
        'token',
        'patrimonio_text',
        'meio_ambiente_text',
        'informacoes_complementares_text'
    ];

    // Mapeia os dados do POST para os campos da tabela
    $valores = [];
    foreach ($colunas as $coluna) {
        $valores[] = verificaTexto($_POST[$coluna] ?? null);
    }

    // Preparar a query adequada (INSERT ou UPDATE)
    if ($isUpdate) {
        $sql = "UPDATE DAT4 SET 
                patrimonio_text = ?,
                meio_ambiente_text = ?,
                informacoes_complementares_text = ? 
                WHERE token = ?";

        // Reorganizar valores para UPDATE
        $updateValores = array_slice($valores, 1); // Remover token
        $updateValores[] = $token; // Adicionar token ao final
        $valores = $updateValores;
    } else {
        // Preparar a query de inserção com placeholders
        $placeholders = implode(', ', array_fill(0, count($colunas), '?'));
        $sql = "INSERT INTO DAT4 (" . implode(', ', $colunas) . ") VALUES ($placeholders)";
    }

    // Preparar a declaração (statement) para evitar SQL Injection
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar consulta: ' . $conn->error, 500);
    }

    // Define os tipos de dados (todos são strings neste caso)
    $tipos = str_repeat('s', count($valores));
    $stmt->bind_param($tipos, ...$valores);

    // Executar a query
    if (!$stmt->execute()) {
        throw new Exception('Erro ao executar consulta: ' . $stmt->error, 500);
    }

    // Verificar se houve alterações
    if ($stmt->affected_rows <= 0 && !$isUpdate) {
        throw new Exception('Nenhum dado foi inserido', 500);
    }

    // Atualizar o status do formulário usando o valor correto do ENUM
    $status = 'completo'; // Valor exato do ENUM
    $sql_update_status = "UPDATE formularios_dat_central SET preenchimento_status = ?, ultima_atualizacao = CURRENT_TIMESTAMP WHERE token = ?";
    $stmt_update = $conn->prepare($sql_update_status);
    if (!$stmt_update) {
        throw new Exception('Erro ao preparar atualização de status: ' . $conn->error, 500);
    }

    $stmt_update->bind_param("ss", $status, $token);
    if (!$stmt_update->execute()) {
        throw new Exception('Erro ao atualizar status do formulário: ' . $stmt_update->error, 500);
    }
    $stmt_update->close();

    // Buscar informações do usuário usando o token
    $sql_usuario = "SELECT nome, email FROM DAT1 WHERE token = ?";
    $stmt_usuario = $conn->prepare($sql_usuario);
    if (!$stmt_usuario) {
        throw new Exception('Erro ao buscar informações do usuário: ' . $conn->error, 500);
    }

    $stmt_usuario->bind_param("s", $token);
    if (!$stmt_usuario->execute()) {
        throw new Exception('Erro ao executar consulta de usuário: ' . $stmt_usuario->error, 500);
    }

    $result = $stmt_usuario->get_result();
    $usuario = $result->fetch_assoc();
    $stmt_usuario->close();

    $nome = $usuario['nome'] ?? 'Usuário';
    $email = $usuario['email'] ?? '';

    // Se o email não for válido, não tenta enviar
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Apenas log o problema mas não falha a operação
        error_log("Email inválido ou não fornecido para o token {$token}: {$email}");
    } else {
        // Salvar os valores originais do POST
        $original_post = $_POST;

        // Configurar os dados para envio de email
        $_POST = array(
            'email' => $email,
            'nome' => $nome,
            'assunto' => "Registro de DAT - Protocolo #" . $token,
            'mensagem' => "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='background-color: #f5f5f5; padding: 20px;'>
                    <h2 style='color: #2c5282;'>DAT Registrado com Sucesso</h2>
                    <p>Prezado(a) {$nome},</p>
                    <p>Seu Documento de Arrecadação de Taxas (DAT) foi registrado com sucesso!</p>
                    <p><strong>Número de Protocolo:</strong> #{$token}</p>
                    <hr style='border: 1px solid #e2e8f0;'>

                    <p><strong>Próximos Passos:</strong></p>
                    <ol style='margin-left: 20px;'>
                        <li>Sua solicitação será processada pelo setor responsável</li>           
                        <li>O documento final deverá ser retirado presencialmente</li>
                        <li>Você será notificado quando o documento estiver pronto</li>
                    </ol>
                    <p><strong>IMPORTANTE:</strong></p>
                    <ul style='margin-left: 20px; color: #e53e3e;'>
                        <li>Este é um e-mail automático, não responda</li>
                        <li>Guarde seu número de protocolo</li>
                        <li>Traga um documento de identificação para retirada</li>
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
        try {
            require_once(__DIR__ . '/../../utils/mail.php');
        } catch (Exception $e) {
            // Apenas log o erro de email, mas não falha a operação principal
            error_log("Erro ao enviar email DAT: " . $e->getMessage());
        }

        // Restaurar os valores originais do POST
        $_POST = $original_post;
    }

    // Retornar resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Formulário completo processado com sucesso',
        'token' => $token,
        'status' => 'completo'
    ]);
} catch (Exception $e) {
    // Log do erro para facilitar a depuração
    error_log("Erro DAT4: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} finally {
    // Fechar as conexões abertas
    if (isset($stmt)) $stmt->close();
    if (isset($conn) && !$conn->connect_error) $conn->close();
}
?>