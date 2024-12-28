<?php
function exibirDetalhesSAC($conn, $id)
{
    $sql = "SELECT * FROM sac WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sac = $result->fetch_assoc();

    if (!$sac) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">Solicitação não encontrada.</span>
              </div>';
        return;
    }

    $dataSubmissao = new DateTime($sac['data_submissao']);
    $tipoContato = ucfirst($sac['tipo_contato']);
?>
    <div class="bg-white shadow rounded-lg p-6">
        <!-- Informações do Solicitante -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Solicitante</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Nome:</span>
                        <?php echo htmlspecialchars($sac['nome']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Email:</span>
                        <?php echo htmlspecialchars($sac['email']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Telefone:</span>
                        <?php echo htmlspecialchars($sac['telefone']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Data de Submissão:</span>
                        <?php echo $dataSubmissao->format('d/m/Y H:i'); ?></p>
                </div>
            </div>
        </div>

        <!-- Detalhes da Solicitação -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Detalhes da <?php echo $tipoContato; ?></h3>
            <div class="space-y-4">
                <p class="text-gray-600"><span class="font-semibold">Assunto:</span>
                    <?php echo htmlspecialchars($sac['assunto']); ?></p>

                <?php if ($sac['mensagem']): ?>
                    <div>
                        <p class="text-gray-600 font-semibold mb-2">Mensagem:</p>
                        <div class="bg-gray-50 rounded-lg p-4 text-gray-700">
                            <?php echo nl2br(htmlspecialchars($sac['mensagem'])); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 rounded-lg p-4 text-gray-500 italic flex items-center justify-center">
                        <i class="material-icons mr-2">info</i>
                        Nenhuma mensagem adicional
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status da Solicitação -->
        <div>
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Status da <?php echo $tipoContato; ?></h3>
            <div class="flex items-center">
                <div class="inline-flex items-center px-4 py-2 rounded-full 
                    <?php echo $sac['situacao'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : ($sac['situacao'] == 'Aprovado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                    <i class="material-icons text-sm mr-2">
                        <?php echo $sac['situacao'] == 'Pendente' ? 'pending' : ($sac['situacao'] == 'Aprovado' ? 'check_circle' : 'cancel'); ?>
                    </i>
                    <span class="text-sm font-semibold"><?php echo htmlspecialchars($sac['situacao']); ?></span>
                </div>
                <span class="ml-3 text-gray-500 text-sm">
                    <?php echo $sac['tipo_contato'] === 'solicitacao' ? 'Solicitação' : 'Reclamação'; ?>
                </span>
            </div>
        </div>
    </div>
<?php
}
?>