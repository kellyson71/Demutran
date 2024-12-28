<?php
function exibirDetalhesRecurso($conn, $id)
{
    $sql = "SELECT * FROM solicitacoes_demutran WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recurso = $result->fetch_assoc();

    if (!$recurso) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">Recurso não encontrado.</span>
              </div>';
        return;
    }

    $dataSubmissao = new DateTime($recurso['data_submissao']);
    $tipoRecurso = ucfirst(str_replace('_', ' ', $recurso['tipo_solicitacao']));
?>
    <div class="bg-white shadow rounded-lg p-6">
        <!-- Informações do Solicitante -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Solicitante</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Nome:</span>
                        <?php echo htmlspecialchars($recurso['nome']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">CPF:</span>
                        <?php echo htmlspecialchars($recurso['cpf']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Endereço:</span>
                        <?php echo htmlspecialchars($recurso['endereco']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Número:</span>
                        <?php echo htmlspecialchars($recurso['numero']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Email:</span>
                        <?php echo htmlspecialchars($recurso['email']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Telefone:</span>
                        <?php echo htmlspecialchars($recurso['telefone']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Bairro:</span>
                        <?php echo htmlspecialchars($recurso['bairro']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">CEP:</span>
                        <?php echo htmlspecialchars($recurso['cep']); ?></p>
                </div>
            </div>
        </div>

        <!-- Informações do Veículo -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Veículo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Placa:</span>
                        <?php echo htmlspecialchars($recurso['placa']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Marca/Modelo:</span>
                        <?php echo htmlspecialchars($recurso['marcaModelo']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Cor:</span>
                        <?php echo htmlspecialchars($recurso['cor']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Espécie:</span>
                        <?php echo htmlspecialchars($recurso['especie']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Categoria:</span>
                        <?php echo htmlspecialchars($recurso['categoria']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Ano:</span>
                        <?php echo htmlspecialchars($recurso['ano']); ?></p>
                </div>
            </div>
        </div>

        <!-- Informações da Infração -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Detalhes da Infração</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Auto de Infração:</span>
                        <?php echo htmlspecialchars($recurso['autoInfracao']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Data da Infração:</span>
                        <?php echo $recurso['dataInfracao'] ? date('d/m/Y', strtotime($recurso['dataInfracao'])) : ''; ?>
                    </p>
                </div>
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Local da Infração:</span>
                        <?php echo htmlspecialchars($recurso['localInfracao']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Enquadramento:</span>
                        <?php echo htmlspecialchars($recurso['enquadramento']); ?></p>
                </div>
            </div>
            <?php if ($recurso['defesa']): ?>
                <div class="mt-4">
                    <p class="text-gray-600"><span class="font-semibold">Defesa:</span></p>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <?php echo nl2br(htmlspecialchars($recurso['defesa'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Documentos Anexados -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Documentos Anexados</h3>
            <?php
            $temDocumentos = false;
            $documentos = [
                'doc_requerimento_url' => ['icon' => 'description', 'label' => 'Requerimento'],
                'cnh_url' => ['icon' => 'card_membership', 'label' => 'CNH'],
                'cnh_condutor_url' => ['icon' => 'person', 'label' => 'CNH do Condutor'],
                'notif_DEMUTRAN_url' => ['icon' => 'notification_important', 'label' => 'Notificação DEMUTRAN'],
                'crlv_url' => ['icon' => 'directions_car', 'label' => 'CRLV'],
                'comprovante_residencia_url' => ['icon' => 'home', 'label' => 'Comprovante de Residência'],
                'assinatura_condutor_url' => ['icon' => 'draw', 'label' => 'Assinatura do Condutor'],
                'assinatura_proprietario_url' => ['icon' => 'draw', 'label' => 'Assinatura do Proprietário']
            ];
            ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($documentos as $campo => $info):
                    if (!empty($recurso[$campo])):
                        $temDocumentos = true; ?>
                        <div class="flex items-center">
                            <i class="material-icons text-blue-600 mr-2"><?php echo $info['icon']; ?></i>
                            <a href="<?php echo htmlspecialchars($recurso[$campo]); ?>" target="_blank"
                                class="text-blue-600 hover:text-blue-800"><?php echo $info['label']; ?></a>
                        </div>
                <?php endif;
                endforeach; ?>

                <?php if (!$temDocumentos): ?>
                    <div class="col-span-2">
                        <div class="bg-gray-50 rounded-lg p-4 text-gray-500 italic flex items-center justify-center">
                            <i class="material-icons mr-2">folder_off</i>
                            Nenhum documento anexado
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status do Recurso -->
        <div class="mt-4">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Status do <?php echo $tipoRecurso; ?></h3>
            <div
                class="inline-flex items-center px-4 py-2 rounded-full 
                <?php echo $recurso['situacao'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : ($recurso['situacao'] == 'Aprovado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                <span class="text-sm font-semibold"><?php echo htmlspecialchars($recurso['situacao']); ?></span>
            </div>
        </div>
    </div>
<?php
}
?>