<?php
require_once __DIR__ . '/helpers.php';

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
            <div class="space-y-4">
                <?php
                echo createEditableField('Nome', $recurso['nome'], 'nome');
                echo createEditableField('Tipo de Requerente', ucfirst($recurso['tipo_requerente']), 'tipo_requerente'); // Nova linha
                echo createEditableField('CPF', $recurso['cpf'], 'cpf');
                echo createEditableField('Email', $recurso['email'], 'email');
                echo createEditableField('Telefone', $recurso['telefone'], 'telefone');
                echo createEditableField('Endereço', $recurso['endereco'], 'endereco');
                echo createEditableField('Número', $recurso['numero'], 'numero');
                echo createEditableField('Bairro', $recurso['bairro'], 'bairro');
                echo createEditableField('CEP', $recurso['cep'], 'cep');
                ?>
            </div>
        </div>

        <!-- Informações do Veículo -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Veículo</h3>
            <div class="space-y-4">
                <?php
                echo createEditableField('Placa', $recurso['placa'], 'placa');
                echo createEditableField('Marca/Modelo', $recurso['marcaModelo'], 'marcaModelo');
                echo createEditableField('Cor', $recurso['cor'], 'cor');
                echo createEditableField('Espécie', $recurso['especie'], 'especie');
                echo createEditableField('Categoria', $recurso['categoria'], 'categoria');
                echo createEditableField('Ano', $recurso['ano'], 'ano');
                ?>
            </div>
        </div>

        <!-- Informações da Infração -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Detalhes da Infração</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Auto de Infração:</span>
                        <?php echo htmlspecialchars($recurso['autoInfracao'] ?? ''); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Data da Infração:</span>
                        <?php echo $recurso['dataInfracao'] ? date('d/m/Y', strtotime($recurso['dataInfracao'])) : ''; ?>
                    </p>
                </div>
                <div>
                    <p class="text-gray-600"><span class="font-semibold">Local da Infração:</span>
                        <?php echo htmlspecialchars($recurso['localInfracao'] ?? ''); ?></p>
                    <p class="text-gray-600"><span class="font-semibold">Enquadramento:</span>
                        <?php echo htmlspecialchars($recurso['enquadramento'] ?? ''); ?></p>
                </div>
            </div>
            <?php if ($recurso['defesa']): ?>
                <div class="mt-4">
                    <p class="text-gray-600"><span class="font-semibold">Defesa:</span></p>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <?php echo nl2br(htmlspecialchars($recurso['defesa'] ?? '')); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Documentos Anexados -->
        <div class="border-b pb-6 mb-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Documentos Anexados</h3>
            <?php
            require_once 'pdf_viewer_modal.php';
            echo getPdfViewerModal();

            $temDocumentos = false;
            $tipoSolicitacao = strtolower($recurso['tipo_solicitacao']);
            $pastaDocumentos = "../midia/{$tipoSolicitacao}/{$recurso['id']}";

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
                        $temDocumentos = true;
                        $nomeArquivo = basename($recurso[$campo]);
                        $caminhoArquivo = $pastaDocumentos . '/' . $nomeArquivo;
                        $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
                ?>
                        <div class="flex items-center">
                            <i class="material-icons text-blue-600 mr-2"><?php echo $info['icon']; ?></i>
                            <button
                                onclick="mostrarDocumento('<?php echo $caminhoArquivo; ?>', '<?php echo $info['label']; ?>', '<?php echo $extensao; ?>')"
                                class="text-blue-600 hover:text-blue-800 cursor-pointer"><?php echo $info['label']; ?></button>
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

        <!-- Modal para visualização de documentos -->
        <div id="documentoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg w-full max-w-6xl mx-4 relative h-[90vh] flex flex-col">
                <!-- Barra de título e ferramentas -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 id="modalTitle" class="text-xl font-semibold"></h3>
                    <div class="flex items-center space-x-4">
                        <button id="zoomOut" class="p-2 hover:bg-gray-100 rounded-full" title="Diminuir Zoom">
                            <i class="material-icons">zoom_out</i>
                        </button>
                        <span id="zoomLevel" class="text-sm">100%</span>
                        <button id="zoomIn" class="p-2 hover:bg-gray-100 rounded-full" title="Aumentar Zoom">
                            <i class="material-icons">zoom_in</i>
                        </button>
                        <button onclick="toggleFullscreen()" class="p-2 hover:bg-gray-100 rounded-full" title="Tela Cheia">
                            <i class="material-icons">fullscreen</i>
                        </button>
                        <button onclick="fecharModal()" class="p-2 hover:bg-gray-100 rounded-full" title="Fechar">
                            <i class="material-icons">close</i>
                        </button>
                    </div>
                </div>

                <!-- Loading spinner -->
                <div id="loadingSpinner"
                    class="hidden absolute inset-0 flex items-center justify-center bg-white bg-opacity-80">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                </div>

                <!-- Container do PDF -->
                <div id="documentoContainer" class="flex-1 overflow-auto relative bg-gray-100">
                    <div id="pdfWrapper" class="min-h-full flex items-center justify-center transform origin-center">
                        <!-- O PDF será carregado aqui -->
                    </div>
                </div>

                <!-- Barra de status -->
                <div class="border-t p-2 bg-gray-50 flex justify-between items-center text-sm text-gray-600">
                    <span id="pageInfo">Página: 1/1</span>
                    <div class="flex items-center space-x-2">
                        <button onclick="downloadDocument()"
                            class="flex items-center px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            <i class="material-icons text-sm mr-1">download</i> Download
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            if (typeof currentDocumentPath === 'undefined') {
                let currentDocumentPath = '';
                let currentZoom = 100;
                const zoomStep = 25;
                const maxZoom = 200;
                const minZoom = 50;

                function mostrarDocumento(caminho, titulo) {
                    currentDocumentPath = caminho;
                    const modal = document.getElementById('documentoModal');
                    const container = document.getElementById('pdfWrapper');
                    const modalTitle = document.getElementById('modalTitle');
                    const loadingSpinner = document.getElementById('loadingSpinner');

                    modalTitle.textContent = titulo;
                    loadingSpinner.classList.remove('hidden');
                    container.innerHTML = '';

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');

                    // Criar o objeto embed para PDF
                    const embed = document.createElement('embed');
                    embed.src = caminho;
                    embed.type = 'application/pdf';
                    embed.style.width = '100%';
                    embed.style.height = '100%';

                    // Evento para esconder o loading quando o PDF carregar
                    embed.onload = () => {
                        loadingSpinner.classList.add('hidden');
                    };

                    container.appendChild(embed);
                    atualizarZoom();
                }

                function fecharModal() {
                    const modal = document.getElementById('documentoModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    currentZoom = 100;
                    atualizarZoom();
                }

                function atualizarZoom() {
                    const wrapper = document.getElementById('pdfWrapper');
                    const zoomLevel = document.getElementById('zoomLevel');
                    wrapper.style.transform = `scale(${currentZoom / 100})`;
                    zoomLevel.textContent = `${currentZoom}%`;
                }

                document.getElementById('zoomIn').addEventListener('click', () => {
                    if (currentZoom < maxZoom) {
                        currentZoom += zoomStep;
                        atualizarZoom();
                    }
                });

                document.getElementById('zoomOut').addEventListener('click', () => {
                    if (currentZoom > minZoom) {
                        currentZoom -= zoomStep;
                        atualizarZoom();
                    }
                });

                function toggleFullscreen() {
                    const container = document.getElementById('documentoContainer');
                    if (!document.fullscreenElement) {
                        container.requestFullscreen().catch(err => {
                            console.error(`Erro ao tentar entrar em tela cheia: ${err.message}`);
                        });
                    } else {
                        document.exitFullscreen();
                    }
                }

                function downloadDocument() {
                    if (currentDocumentPath) {
                        const link = document.createElement('a');
                        link.href = currentDocumentPath;
                        link.download = currentDocumentPath.split('/').pop();
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                }

                // Fechar modal ao clicar fora
                document.getElementById('documentoModal').addEventListener('click', function(e) {
                    if (e.target === this) fecharModal();
                });

                // Atalhos de teclado
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !document.getElementById('documentoModal').classList.contains('hidden')) {
                        fecharModal();
                    }
                });
            }
        </script>

        <!-- Status do Recurso -->
        <div class="mt-4">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Status do <?php echo $tipoRecurso; ?></h3>
            <div
                class="inline-flex items-center px-4 py-2 rounded-full 
                <?php echo $recurso['situacao'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : ($recurso['situacao'] == 'Aprovado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                <span class="text-sm font-semibold"><?php echo htmlspecialchars($recurso['situacao'] ?? ''); ?></span>
            </div>
        </div>
    </div>
<?php
}
?>