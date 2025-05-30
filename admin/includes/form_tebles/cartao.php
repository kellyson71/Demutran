<?php
require_once __DIR__ . '/helpers.php';

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

    // Inclui o componente do PDF Viewer
    require_once 'pdf_viewer_modal.php';
    echo getPdfViewerModal();
?>
    <div class="bg-white shadow rounded-lg p-6">
        <!-- Informações do Cartão -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Cartão</h3>
            <div class="space-y-4">
                <?php
                echo createEditableField('Tipo', ucfirst($cartao['tipo_solicitacao']), 'tipo_solicitacao');
                echo createEditableField('Emissão', ucfirst($cartao['emissao_cartao']), 'emissao_cartao');
                echo createEditableField('Nº do Cartão', $cartao['n_cartao'] ?? 'Não atribuído', 'n_cartao');
                ?>
            </div>
        </div>

        <!-- Informações Pessoais -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações Pessoais</h3>
            <div class="space-y-4">
                <?php
                echo createEditableField('Nome', $cartao['nome'], 'nome');
                echo createEditableField('CPF', $cartao['cpf'], 'cpf');
                echo createEditableField('Email', $cartao['email'], 'email');
                echo createEditableField('Telefone', $cartao['telefone'], 'telefone');
                echo createEditableField('Endereço', $cartao['endereco'], 'endereco');
                ?>
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
            $pastaDocumentos = "../midia/cartao/{$cartao['id']}";
            $errosDocumentos = [];

            // Verifica se a pasta existe, se não, tenta criar
            if (!file_exists($pastaDocumentos)) {
                if (!mkdir($pastaDocumentos, 0777, true)) {
                    $errosDocumentos[] = "Erro ao criar diretório de documentos.";
                }
            }

            $documentos = [
                'doc_identidade_url' => ['icon' => 'description', 'label' => 'Documento de Identidade'],
                'comprovante_residencia_url' => ['icon' => 'home', 'label' => 'Comprovante de Residência'],
                'laudo_medico_url' => ['icon' => 'medical_services', 'label' => 'Laudo Médico'],
                'doc_identidade_representante_url' => ['icon' => 'person', 'label' => 'Documento do Representante'],
                'proc_comprovante_url' => ['icon' => 'gavel', 'label' => 'Procuração/Comprovante']
            ];
            ?>

            <?php if (!empty($errosDocumentos)): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="material-icons text-red-400">warning</i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Problemas encontrados:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php foreach ($errosDocumentos as $erro): ?>
                                        <li><?php echo htmlspecialchars($erro); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php
                foreach ($documentos as $campo => $info):
                    if (!empty($cartao[$campo])):
                        $nomeArquivo = basename($cartao[$campo]);
                        $caminhoArquivo = $pastaDocumentos . '/' . $nomeArquivo;
                        $arquivoExiste = file_exists($caminhoArquivo);

                        if ($arquivoExiste):
                            $temDocumentos = true;
                ?>
                            <div class="flex items-center">
                                <i class="material-icons text-blue-600 mr-2"><?php echo $info['icon']; ?></i>
                                <button onclick="mostrarDocumento('<?php echo $caminhoArquivo; ?>', '<?php echo $info['label']; ?>')"
                                    class="text-blue-600 hover:text-blue-800 cursor-pointer">
                                    <?php echo $info['label']; ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center">
                                <i class="material-icons text-red-600 mr-2">error</i>
                                <span class="text-red-600">
                                    <?php echo $info['label']; ?> (arquivo não encontrado)
                                </span>
                            </div>
                <?php
                        endif;
                    endif;
                endforeach;
                ?>

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