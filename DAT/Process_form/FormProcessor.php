<?php
class FormProcessor
{
    private $conn;
    private $config;
    private $formType;
    private $token;
    private $inputData;
    private $response = [
        'success' => false,
        'message' => ''
    ];

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function process($formType, $token, $inputData = null)
    {
        try {
            $this->formType = $formType;
            $this->token = $token;

            // Se nenhum inputData for fornecido, usamos $_POST
            $this->inputData = $inputData ?? $_POST;

            // Carregar a configuração do formulário
            $this->loadConfig();

            // Verificar token na tabela central
            if (!$this->verifyToken()) {
                return $this->response;
            }

            // Validar campos obrigatórios
            if (!$this->validateRequiredFields()) {
                return $this->response;
            }

            // Processar os dados (INSERT ou UPDATE)
            if (!$this->processData()) {
                return $this->response;
            }

            // Atualizar status central
            if (isset($this->config['status_apos_processo']) && !empty($this->config['status_apos_processo'])) {
                $this->updateCentralStatus($this->config['status_apos_processo']);
            }

            // Enviar e-mail se configurado
            if (isset($this->config['enviar_email']) && $this->config['enviar_email']) {
                $this->sendEmail();
            }

            $this->response['success'] = true;
            $this->response['message'] = 'Dados processados com sucesso!';

            return $this->response;
        } catch (Exception $e) {
            $this->response['message'] = "Erro: " . $e->getMessage();
            return $this->response;
        }
    }

    private function loadConfig()
    {
        $configFile = __DIR__ . "/config/{$this->formType}.php";

        if (!file_exists($configFile)) {
            throw new Exception("Configuração para o formulário {$this->formType} não encontrada.");
        }

        $this->config = include $configFile;
    }

    private function verifyToken()
    {
        $stmt = $this->conn->prepare("SELECT id FROM formularios_dat_central WHERE token = ?");
        $stmt->bind_param("s", $this->token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $this->response['message'] = "Token inválido ou formulário não encontrado!";
            $stmt->close();
            return false;
        }

        $stmt->close();
        return true;
    }

    private function validateRequiredFields()
    {
        $missingFields = [];

        foreach ($this->config['campos'] as $campo) {
            if (isset($campo['obrigatorio']) && $campo['obrigatorio']) {
                $nomeCampo = $campo['nome'];

                // Verifica se o campo existe e não está vazio
                if (
                    !isset($this->inputData[$nomeCampo]) ||
                    (empty($this->inputData[$nomeCampo]) && $this->inputData[$nomeCampo] !== '0' && $this->inputData[$nomeCampo] !== 0)
                ) {
                    $missingFields[] = $nomeCampo;
                }
            }
        }

        if (!empty($missingFields)) {
            $this->response['message'] = "Campos obrigatórios não preenchidos: " . implode(", ", $missingFields);
            return false;
        }

        return true;
    }

    private function processData()
    {
        $tableName = $this->config['tabela'];
        $campos = $this->config['campos'];

        // Inicializa arrays para os nomes de campos, placeholders, valores e tipos
        $fieldNames = [];
        $placeholders = [];
        $updateStatements = [];
        $values = [];
        $types = '';

        // Prepara os dados para inserção/atualização
        foreach ($campos as $campo) {
            $nomeCampo = $campo['nome'];
            $fieldNames[] = $nomeCampo;
            $placeholders[] = '?';
            $updateStatements[] = "$nomeCampo = ?";

            $valor = $this->inputData[$nomeCampo] ?? null;

            // Processa o valor de acordo com o tipo
            if (isset($campo['tipo'])) {
                switch ($campo['tipo']) {
                    case 'boolean':
                        $valor = isset($this->inputData[$nomeCampo]) ? 1 : 0;
                        $types .= 'i';
                        break;
                    case 'integer':
                        $valor = !empty($valor) ? intval($valor) : null;
                        $types .= 'i';
                        break;
                    case 'float':
                        $valor = !empty($valor) ? floatval($valor) : null;
                        $types .= 'd';
                        break;
                    default: // string e outros casos
                        $types .= 's';
                }
            } else {
                // Default para string
                $types .= 's';
            }

            // Aplica valor padrão se necessário
            if ($valor === null && isset($campo['padrao'])) {
                $valor = $campo['padrao'];
            }

            $values[] = $valor;
        }

        // Adiciona o token aos campos se não estiver já incluído
        if (!in_array('token', $fieldNames)) {
            $fieldNames[] = 'token';
            $placeholders[] = '?';
            $updateStatements[] = "token = ?";
            $values[] = $this->token;
            $types .= 's';
        }

        // Verifica se deve verificar existência do registro
        $existingId = null;
        if (isset($this->config['verificar_existente']) && $this->config['verificar_existente']) {
            $stmt = $this->conn->prepare("SELECT id FROM {$tableName} WHERE token = ?");
            $stmt->bind_param('s', $this->token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $existingId = $result->fetch_object()->id;
            }
            $stmt->close();
        }

        // Inicia uma transação
        $this->conn->begin_transaction();

        try {
            if ($existingId) {
                // UPDATE
                $sql = "UPDATE {$tableName} SET " . implode(', ', $updateStatements) . " WHERE token = ?";
                $stmt = $this->conn->prepare($sql);

                // Adiciona o token para a cláusula WHERE
                $values[] = $this->token;
                $types .= 's';
            } else {
                // INSERT
                $sql = "INSERT INTO {$tableName} (" . implode(', ', $fieldNames) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $this->conn->prepare($sql);
            }

            // Bind dos parâmetros de forma dinâmica
            if ($stmt) {
                $bindParams = array(&$types);
                foreach ($values as $i => $value) {
                    $bindParams[] = &$values[$i];
                }

                call_user_func_array(array($stmt, 'bind_param'), $bindParams);

                if (!$stmt->execute()) {
                    throw new Exception("Erro ao executar SQL: " . $stmt->error);
                }

                $stmt->close();
                $this->conn->commit();
                return true;
            } else {
                throw new Exception("Erro ao preparar SQL: " . $this->conn->error);
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->response['message'] = $e->getMessage();
            return false;
        }
    }

    private function updateCentralStatus($status)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE formularios_dat_central SET preenchimento_status = ? WHERE token = ?");
            $stmt->bind_param("ss", $status, $this->token);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Erro ao atualizar status central: " . $e->getMessage());
        }
    }

    private function sendEmail()
    {
        try {
            // Obter informações do usuário usando o token
            $stmt = $this->conn->prepare("SELECT nome, email FROM DAT1 WHERE token = ?");
            $stmt->bind_param("s", $this->token);
            $stmt->execute();
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            $stmt->close();

            $nome = $usuario['nome'] ?? 'Usuário';
            $email = $usuario['email'] ?? '';

            if (empty($email)) {
                throw new Exception("E-mail do usuário não encontrado.");
            }

            // Salva os valores originais do POST
            $original_post = $_POST;

            // Configura os dados para envio de email
            $_POST = array(
                'email' => $email,
                'nome' => $nome,
                'assunto' => "Registro de DAT - Protocolo #" . $this->token,
                'mensagem' => "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <div style='background-color: #f5f5f5; padding: 20px;'>
                        <h2 style='color: #2c5282;'>DAT Registrado com Sucesso</h2>
                        <p>Prezado(a) {$nome},</p>
                        <p>Seu Documento de Arrecadação de Taxas (DAT) foi registrado com sucesso!</p>
                        <p><strong>Número de Protocolo:</strong> #{$this->token}</p>
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
            require_once(__DIR__ . '/../../utils/mail.php');

            // Restaura os valores originais do POST
            $_POST = $original_post;
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail: " . $e->getMessage());
        }
    }
}
