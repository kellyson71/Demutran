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

    // Inclui o componente do PDF Viewer
    require_once 'pdf_viewer_modal.php';
    echo getPdfViewerModal();
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
            <?php
            $temDocumentos = false;
            $pastaDocumentos = "../midia/parecer/{$parecer['id']}";
            $errosDocumentos = [];

            // Verifica se a pasta existe, se não, tenta criar
            if (!file_exists($pastaDocumentos)) {
                if (!mkdir($pastaDocumentos, 0777, true)) {
                    $errosDocumentos[] = "Erro ao criar diretório de documentos.";
                }
            }

            $documentos = [
                'documento_identificacao' => ['icon' => 'description', 'label' => 'Documento de Identificação'],
                'comprovante_residencia' => ['icon' => 'home', 'label' => 'Comprovante de Residência'],
                'signed_form_path' => ['icon' => 'draw', 'label' => 'Formulário Assinado']
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
                    if (!empty($parecer[$campo])):
                        $nomeArquivo = basename($parecer[$campo]);
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