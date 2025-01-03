<?php
require_once(__DIR__ . '/../../env/config.php');

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

function verificaTexto($valor) {
    return isset($valor) && !empty($valor) ? $valor : "não informado";
}

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

// Preparar a query de inserção com placeholders
$placeholders = implode(', ', array_fill(0, count($colunas), '?'));
$sql = "INSERT INTO DAT4 (" . implode(', ', $colunas) . ") VALUES ($placeholders)";

// Preparar a declaração (statement) para evitar SQL Injection
$stmt = $conn->prepare($sql);

// Define os tipos de dados (todos são strings neste caso)
$tipos = str_repeat('s', count($valores));
$stmt->bind_param($tipos, ...$valores);

// Executar a query
if ($stmt->execute()) {
    // Atualizar o status do formulário usando o valor correto do ENUM
    $token = $_POST['token'];
    $status = 'completo'; // Valor exato do ENUM
    $sql_update_status = "UPDATE formularios_dat_central SET preenchimento_status = ? WHERE token = ?";
    $stmt_update = $conn->prepare($sql_update_status);
    $stmt_update->bind_param("ss", $status, $token);

    if (!$stmt_update->execute()) {
        error_log("Erro ao atualizar status do formulário: " . $stmt_update->error);
    }
    $stmt_update->close();

    // Buscar informações do usuário usando o token
    $sql_usuario = "SELECT nome, email FROM DAT1 WHERE token = ?";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("s", $token);
    $stmt_usuario->execute();
    $result = $stmt_usuario->get_result();
    $usuario = $result->fetch_assoc();

    $nome = $usuario['nome'] ?? 'Usuário';
    $email = $usuario['email'] ?? '';

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
        error_log("Enviando email para DAT: " . $email);
    } catch (Exception $e) {
        error_log("Erro ao enviar email DAT: " . $e->getMessage());
    }

    // Restaurar os valores originais do POST
    $_POST = $original_post;

    echo "Dados inseridos com sucesso!";
} else {
    echo "Erro: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>