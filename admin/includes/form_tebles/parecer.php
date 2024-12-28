<?php
function exibirDetalhesParecer($conn, $id)
{
    // Buscar dados do parecer
    $sql = "SELECT * FROM Parecer WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $parecer = $result->fetch_assoc();

    if (!$parecer) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">Parecer não encontrado.</span>
              </div>';
        return;
    }

    // Formatar data e hora
    $dataSubmissao = new DateTime($parecer['data_submissao']);
?>

    <div class="bg-white shadow rounded-lg p-6">
        <!-- Informações Básicas -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Solicitante</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Nome:</span>
                        <?php echo htmlspecialchars($parecer['nome']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">CPF/CNPJ:</span>
                        <?php echo htmlspecialchars($parecer['cpf_cnpj']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Email:</span>
                        <?php echo htmlspecialchars($parecer['email']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Telefone:</span>
                        <?php echo htmlspecialchars($parecer['telefone']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Protocolo:</span>
                        <?php echo htmlspecialchars($parecer['protocolo']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Data de Submissão:</span>
                        <?php echo $dataSubmissao->format('d/m/Y H:i'); ?></p>
                </div>
            </div>
        </div>

        <!-- Detalhes do Evento -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Detalhes do Evento</h3>
            <div class="grid grid-cols-1 gap-4">
                <p class="text-gray-600"><span class="font-semibold">Local:</span>
                    <?php echo htmlspecialchars($parecer['local']); ?></p>
                <p class="text-gray-600"><span class="font-semibold">Evento:</span>
                    <?php echo htmlspecialchars($parecer['evento']); ?></p>
                <p class="text-gray-600"><span class="font-semibold">Ponto de Referência:</span>
                    <?php echo htmlspecialchars($parecer['ponto_referencia']); ?></p>
                <p class="text-gray-600"><span class="font-semibold">Data e Horário:</span>
                    <?php echo htmlspecialchars($parecer['data_horario']); ?></p>
            </div>
        </div>

        <!-- Documentos Anexados -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Documentos Anexados</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if ($parecer['documento_identificacao']): ?>
                    <div class="flex items-center">
                        <i class="material-icons text-blue-600 mr-2">description</i>
                        <a href="<?php echo htmlspecialchars($parecer['documento_identificacao']); ?>" target="_blank"
                            class="text-blue-600 hover:text-blue-800">Documento de Identificação</a>
                    </div>
                <?php endif; ?>

                <?php if ($parecer['comprovante_residencia']): ?>
                    <div class="flex items-center">
                        <i class="material-icons text-blue-600 mr-2">home</i>
                        <a href="<?php echo htmlspecialchars($parecer['comprovante_residencia']); ?>" target="_blank"
                            class="text-blue-600 hover:text-blue-800">Comprovante de Residência</a>
                    </div>
                <?php endif; ?>

                <?php if ($parecer['signed_form_path']): ?>
                    <div class="flex items-center">
                        <i class="material-icons text-blue-600 mr-2">draw</i>
                        <a href="<?php echo htmlspecialchars($parecer['signed_form_path']); ?>" target="_blank"
                            class="text-blue-600 hover:text-blue-800">Formulário Assinado</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status do Parecer -->
        <div>
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Status do Parecer</h3>
            <div
                class="inline-flex items-center px-4 py-2 rounded-full 
                        <?php echo $parecer['situacao'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : ($parecer['situacao'] == 'Aprovado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                <span class="text-sm font-semibold"><?php echo htmlspecialchars($parecer['situacao']); ?></span>
            </div>
        </div>
    </div>
<?php
}
?>