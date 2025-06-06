<?php
session_start();
include '../env/config.php';

// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados enviados via JSON
    $data = json_decode(file_get_contents("php://input"), true);

    // Extrai os dados recebidos
    $id = $data['id'];
    $tipo = $data['tipo'];
    $campo = $data['campo'];
    $novoValor = $data['novoValor'];

    // Verifica se os dados necessários estão presentes
    if (!empty($id) && !empty($tipo) && !empty($campo) && !empty($novoValor)) {
        // Determina a tabela correta com base no tipo de formulário
        if ($tipo == 'SAC') {
            $tabela = 'sac';
        } elseif ($tipo == 'JARI') {
            $tabela = 'solicitacoes_demutran';
        } elseif ($tipo == 'PCD') {
            $tabela = 'solicitacao_cartao';
        } elseif ($tipo == 'DAT') {
            $tabela = 'DAT1';
        } elseif ($tipo == 'Parecer') {   // Adicionado suporte para 'Parecer'
            $tabela = 'Parecer';
        } else {
            echo json_encode(['success' => false, 'message' => 'Tipo de formulário inválido.']);
            exit();
        }

        // Prepara a consulta SQL para atualizar o campo
        $sql = "UPDATE $tabela SET $campo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $novoValor, $id);

        // Executa a consulta e verifica o resultado
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Formulário atualizado com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o formulário.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}
?>