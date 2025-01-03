<?php
function getPdfViewerModal()
{
    ob_start();
?>
    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>

    <!-- Modal para visualização de documentos -->
    <div id="documentoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-7xl mx-4 relative h-[95vh] flex flex-col">
            <!-- Barra de título e ferramentas -->
            <div class="flex items-center justify-between p-4 border-b bg-gray-50">
                <h3 id="modalTitle" class="text-xl font-semibold"></h3>
                <div class="flex items-center space-x-4">
                    <!-- Navegação entre páginas -->
                    <div class="flex items-center space-x-2 mr-4">
                        <button id="prevPage" class="p-2 hover:bg-gray-200 rounded-full" title="Página Anterior">
                            <i class="material-icons">navigate_before</i>
                        </button>
                        <span id="pageNum" class="text-sm">0</span>
                        <span class="text-sm">/</span>
                        <span id="pageCount" class="text-sm">0</span>
                        <button id="nextPage" class="p-2 hover:bg-gray-200 rounded-full" title="Próxima Página">
                            <i class="material-icons">navigate_next</i>
                        </button>
                    </div>
                    <!-- Controles de zoom -->
                    <button id="zoomOut" class="p-2 hover:bg-gray-200 rounded-full" title="Diminuir Zoom">
                        <i class="material-icons">zoom_out</i>
                    </button>
                    <span id="zoomLevel" class="text-sm">100%</span>
                    <button id="zoomIn" class="p-2 hover:bg-gray-200 rounded-full" title="Aumentar Zoom">
                        <i class="material-icons">zoom_in</i>
                    </button>
                    <button onclick="toggleFullscreen()" class="p-2 hover:bg-gray-200 rounded-full" title="Tela Cheia">
                        <i class="material-icons">fullscreen</i>
                    </button>
                    <button onclick="fecharModal()" class="p-2 hover:bg-gray-200 rounded-full" title="Fechar">
                        <i class="material-icons">close</i>
                    </button>
                </div>
            </div>

            <!-- Loading spinner -->
            <div id="loadingSpinner"
                class="hidden absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-50">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <!-- Container do PDF -->
            <div id="documentoContainer" class="flex-1 bg-gray-100 relative overflow-auto">
                <canvas id="pdfCanvas" class="mx-auto"></canvas>
                <!-- Aviso de ESC -->
                <div id="escapeHint"
                    class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-75 text-white px-4 py-2 rounded-full text-sm flex items-center space-x-2 transition-opacity duration-300">
                    <i class="material-icons text-sm">keyboard</i>
                    <span>Pressione ESC para sair da tela cheia</span>
                </div>
            </div>

            <!-- Barra de status -->
            <div class="border-t p-3 bg-gray-50 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <button id="rotateCCW" class="p-2 hover:bg-gray-200 rounded-full" title="Girar Anti-horário">
                        <i class="material-icons">rotate_left</i>
                    </button>
                    <button id="rotateCW" class="p-2 hover:bg-gray-200 rounded-full" title="Girar Horário">
                        <i class="material-icons">rotate_right</i>
                    </button>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="downloadDocument()"
                        class="flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                        <i class="material-icons text-sm mr-2">download</i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let pdfDoc = null;
        let pageNum = 1;
        let pageRendering = false;
        let pageNumPending = null;
        let scale = 1.5;
        let rotation = 0;
        let currentDocumentPath = '';

        function renderPage(num) {
            pageRendering = true;
            document.getElementById('loadingSpinner').classList.remove('hidden');

            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({
                    scale: scale,
                    rotation: rotation
                });
                const canvas = document.getElementById('pdfCanvas');
                const ctx = canvas.getContext('2d');

                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };

                const renderTask = page.render(renderContext);

                renderTask.promise.then(function() {
                    pageRendering = false;
                    document.getElementById('loadingSpinner').classList.add('hidden');

                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
            });

            document.getElementById('pageNum').textContent = num;
        }

        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        function onPrevPage() {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        }

        function onNextPage() {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        }

        function mostrarDocumento(caminho, titulo) {
            currentDocumentPath = caminho;
            pageNum = 1;
            scale = 1.5;
            rotation = 0;

            const modal = document.getElementById('documentoModal');
            const modalTitle = document.getElementById('modalTitle');
            const loadingSpinner = document.getElementById('loadingSpinner');

            modalTitle.textContent = titulo;
            loadingSpinner.classList.remove('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            pdfjsLib.getDocument(caminho).promise.then(function(pdf) {
                pdfDoc = pdf;
                document.getElementById('pageCount').textContent = pdf.numPages;
                renderPage(pageNum);
            });
        }

        function changeZoom(delta) {
            scale += delta;
            scale = Math.max(0.5, Math.min(3, scale));
            document.getElementById('zoomLevel').textContent = `${Math.round(scale * 100)}%`;
            queueRenderPage(pageNum);
        }

        function rotate(delta) {
            rotation += delta;
            rotation = rotation % 360;
            queueRenderPage(pageNum);
        }

        function fecharModal() {
            const modal = document.getElementById('documentoModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Resetar zoom e rotação
            scale = 1.5;
            rotation = 0;
            if (pdfDoc) {
                queueRenderPage(pageNum);
            }
        }

        function toggleFullscreen() {
            const container = document.getElementById('documentoContainer');
            const escHint = document.getElementById('escapeHint');

            if (!document.fullscreenElement) {
                container.requestFullscreen().then(() => {
                    escHint.classList.remove('hidden');
                    setTimeout(() => {
                        escHint.classList.add('hidden');
                    }, 3000); // Esconde após 3 segundos
                }).catch(err => {
                    console.error(`Erro ao tentar entrar em tela cheia: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
                escHint.classList.add('hidden');
            }
        }

        // Adicionar listener para mudanças no estado de tela cheia
        document.addEventListener('fullscreenchange', function() {
            const escHint = document.getElementById('escapeHint');
            if (!document.fullscreenElement) {
                escHint.classList.add('hidden');
            }
        });

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

        // Event Listeners
        document.getElementById('prevPage').addEventListener('click', onPrevPage);
        document.getElementById('nextPage').addEventListener('click', onNextPage);
        document.getElementById('zoomIn').addEventListener('click', () => changeZoom(0.25));
        document.getElementById('zoomOut').addEventListener('click', () => changeZoom(-0.25));
        document.getElementById('rotateCW').addEventListener('click', () => rotate(90));
        document.getElementById('rotateCCW').addEventListener('click', () => rotate(-90));

        // Atalhos de teclado
        document.addEventListener('keydown', function(e) {
            if (!document.getElementById('documentoModal').classList.contains('hidden')) {
                if (e.key === 'ArrowLeft') onPrevPage();
                if (e.key === 'ArrowRight') onNextPage();
                if (e.key === 'Escape') fecharModal();
            }
        });

        // Fechar modal ao clicar fora
        document.getElementById('documentoModal').addEventListener('click', function(e) {
            if (e.target === this) fecharModal();
        });
    </script>
<?php
    return ob_get_clean();
}
?>