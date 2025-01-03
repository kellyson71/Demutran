<?php
function exibirDetalhesCartao($conn, $id)
{
    $sql = "SELECT * FROM solicitacao_cartao WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartao = $result->fetch_assoc();

    if (!$cartao) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">Solicitação não encontrada.</span>
              </div>';
        return;
    }

    $dataSubmissao = new DateTime($cartao['data_submissao']);
    $dataNascimento = new DateTime($cartao['data_nascimento']);
?>
    <div class="bg-white shadow rounded-lg p-6">
        <!-- Informações do Cartão -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Cartão</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Tipo:</span>
                        <?php echo ucfirst(htmlspecialchars($cartao['tipo_solicitacao'])); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Emissão:</span>
                        <?php echo ucfirst(htmlspecialchars($cartao['emissao_cartao'])); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Nº do Cartão:</span>
                        <?php echo htmlspecialchars($cartao['n_cartao'] ?? 'Não atribuído'); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Residente:</span>
                        <?php echo $cartao['residente'] ? 'Sim' : 'Não'; ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Solicitante:</span>
                        <?php echo htmlspecialchars($cartao['solicitante']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Data de Submissão:</span>
                        <?php echo $dataSubmissao->format('d/m/Y H:i'); ?></p>
                </div>
            </div>
        </div>

        <!-- Informações Pessoais -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações Pessoais</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Nome:</span>
                        <?php echo htmlspecialchars($cartao['nome']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">CPF:</span>
                        <?php echo htmlspecialchars($cartao['cpf']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Data de Nascimento:</span>
                        <?php echo $dataNascimento->format('d/m/Y'); ?></p>
                </div>
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Email:</span>
                        <?php echo htmlspecialchars($cartao['email']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Telefone:</span>
                        <?php echo htmlspecialchars($cartao['telefone']); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Endereço:</span>
                        <?php echo htmlspecialchars($cartao['endereco']); ?></p>
                </div>
            </div>
        </div>

        <?php if ($cartao['representante_legal']): ?>
            <!-- Informações do Representante -->
            <div class="border-b pb-6 mb-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Representante Legal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600"><span class="font-semibold">Nome:</span>
                            <?php echo htmlspecialchars($cartao['nome_representante']); ?></p>
                        <p class="text-gray-600"><span class="font-semibold">CPF:</span>
                            <?php echo htmlspecialchars($cartao['cpf_representante']); ?></p>
                        <p class="text-gray-600"><span class="font-semibold">Endereço:</span>
                            <?php echo htmlspecialchars($cartao['endereco_representante']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600"><span class="font-semibold">Email:</span>
                            <?php echo htmlspecialchars($cartao['email_representante']); ?></p>
                        <p class="text-gray-600"><span class="font-semibold">Telefone:</span>
                            <?php echo htmlspecialchars($cartao['telefone_representante']); ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="border-b pb-6 mb-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Representante Legal</h3>
                <div class="bg-gray-50 rounded-lg p-4 text-gray-500 italic flex items-center justify-center">
                    <i class="material-icons mr-2">info</i>
                    Não há representante legal cadastrado
                </div>
            </div>
        <?php endif; ?>

        <!-- Documentos Anexados -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Documentos Anexados</h3>
            <?php
            $temDocumentos = false;
            $baseUrl = "https://{$_SERVER['HTTP_HOST']}/midia/cartao/{$cartao['id']}";

            $documentos = [
                'doc_identidade_url' => ['icon' => 'description', 'label' => 'Documento de Identidade'],
                'comprovante_residencia_url' => ['icon' => 'home', 'label' => 'Comprovante de Residência'],
                'laudo_medico_url' => ['icon' => 'medical_services', 'label' => 'Laudo Médico'],
                'doc_identidade_representante_url' => ['icon' => 'person', 'label' => 'Documento do Representante'],
                'proc_comprovante_url' => ['icon' => 'gavel', 'label' => 'Procuração/Comprovante']
            ];
            ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($documentos as $campo => $info):
                    if (!empty($cartao[$campo])):
                        $temDocumentos = true; ?>
                        <div class="flex items-center">
                            <i class="material-icons text-blue-600 mr-2"><?php echo $info['icon']; ?></i>
                            <a href="<?php echo $baseUrl . '/' . basename($cartao[$campo]); ?>" target="_blank"
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

        <!-- Status da Solicitação -->
        <div>
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Status da Solicitação</h3>
            <div class="inline-flex items-center px-4 py-2 rounded-full 
                        <?php echo $cartao['situacao'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : ($cartao['situacao'] == 'Aprovado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                <span class="text-sm font-semibold"><?php echo htmlspecialchars($cartao['situacao']); ?></span>
            </div>
        </div>
    </div>
<?php
}
?>