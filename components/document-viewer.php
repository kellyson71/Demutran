<?php
function processDocuments($dados, $tipo_form)
{
    if (empty($dados['id'])) return [];

    $documentos = [];
    $id = $dados['id'];
    $pasta = "../../midia/{$tipo_form}/{$id}/";

    // Registra log para debug
    error_log("Verificando pasta: " . realpath($pasta));

    // Verifica se o diretório existe
    if (!is_dir($pasta)) {
        error_log("Diretório não encontrado: " . $pasta);
        return ['error' => 'Pasta não encontrada'];
    }

    // Lê todos os arquivos do diretório
    $files = scandir($pasta);
    if ($files === false) {
        error_log("Erro ao ler diretório: " . $pasta);
        return ['error' => 'Erro ao ler pasta'];
    }

    foreach ($files as $index => $file) {
        // Ignora . e ..
        if ($file === '.' || $file === '..') continue;

        // Pega apenas arquivos PDF, JPG, JPEG e PNG
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
            $documentos[] = [
                'path' => $pasta . $file,
                'name' => $file,
                'label' => 'Documento ' . ($index)
            ];
        }
    }

    error_log("Documentos encontrados: " . count($documentos));
    return $documentos;
}

function renderDocumentViewer($documentos)
{
    ob_start();

    // Se houver erro ou nenhum documento, mostra mensagem apropriada
    if (empty($documentos) || isset($documentos['error'])) {
?>
        <div class="alert alert-info no-print" style="margin-top: 20px; @media print { display: none !important; }">
            <?php
            if (isset($documentos['error'])) {
                echo "Não foi possível localizar os documentos anexados.";
            } else {
                echo "Nenhum documento foi anexado a esta solicitação.";
            }
            ?>
        </div>
    <?php
        return ob_get_clean();
    }

    // Se encontrou documentos, continua com o código normal
    ?>
    <!-- PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>

    <div style="page-break-before: always;"></div>
    <div class="section-title mt-4">DOCUMENTOS ANEXADOS</div>
    <?php foreach ($documentos as $index => $documento): ?>
        <?php
        // Verifica se o arquivo existe antes de tentar renderizar
        if (!file_exists($documento['path'])) {
            continue;
        }

        $ext = strtolower(pathinfo($documento['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])):
            if ($ext === 'pdf'):
        ?>
                <div style="margin: 20px 0; page-break-inside: avoid;">
                    <div id="pdf-container-<?php echo $index; ?>" class="pdf-container"></div>
                </div>
                <script>
                    // Função para renderizar PDF
                    async function renderPDF<?php echo $index; ?>() {
                        try {
                            const loadingTask = pdfjsLib.getDocument('<?php echo $documento['path']; ?>');
                            const pdf = await loadingTask.promise;

                            const container = document.getElementById('pdf-container-<?php echo $index; ?>');

                            // Renderizar cada página do PDF
                            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                                const page = await pdf.getPage(pageNum);
                                const viewport = page.getViewport({
                                    scale: 1.5
                                });

                                const canvas = document.createElement('canvas');
                                canvas.style.display = 'block';
                                canvas.style.margin = '10px auto';
                                container.appendChild(canvas);

                                const context = canvas.getContext('2d');
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;

                                await page.render({
                                    canvasContext: context,
                                    viewport: viewport
                                }).promise;

                                if (pageNum < pdf.numPages) {
                                    const pageBreak = document.createElement('div');
                                    pageBreak.style.pageBreakAfter = 'always';
                                    container.appendChild(pageBreak);
                                }
                            }
                        } catch (error) {
                            console.error('Erro ao renderizar PDF:', error);
                            document.getElementById('pdf-container-<?php echo $index; ?>').innerHTML =
                                '<p class="no-print">Erro ao carregar o PDF. <a href="<?php echo $documento['path']; ?>" target="_blank">Clique aqui para baixar</a></p>';
                        }
                    }
                    renderPDF<?php echo $index; ?>();
                </script>
            <?php else: ?>
                <div style="margin: 20px 0; page-break-inside: avoid;">
                    <img src="<?php echo $documento['path']; ?>"
                        style="max-width: 100%; height: auto; display: block; margin: 0 auto;"
                        onerror="this.style.display='none'; this.parentElement.innerHTML='<p class=\'no-print\'>Erro ao carregar imagem</p>'">
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
<?php
    return ob_get_clean();
}
?>