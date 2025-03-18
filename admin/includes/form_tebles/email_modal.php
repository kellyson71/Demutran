<!-- Modal de Preview do Email -->
<div id="emailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-3/4 max-w-4xl max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Confirmação de Envio</h3>
            <button onclick="closeEmailModal()" class="text-gray-500 hover:text-gray-700">
                <i class="material-icons">close</i>
            </button>
        </div>

        <!-- Preview do Email -->
        <div class="flex-grow overflow-auto mb-4">
            <div class="border rounded-md p-4 bg-gray-50">
                <div class="flex items-center justify-between mb-4">
                    <div class="space-y-2 w-full">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-700">Para:</span>
                            <span id="previewTo" class="text-sm text-gray-600"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-700">Assunto:</span>
                            <span id="previewSubject" class="text-sm text-gray-600"></span>
                        </div>
                    </div>
                    <button onclick="showEditForm()"
                        class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 flex items-center gap-1">
                        <i class="material-icons text-sm">edit</i>
                        <span class="text-sm">Editar</span>
                    </button>
                </div>
                <div class="border-t pt-4">
                    <div id="emailPreview" class="prose max-w-none text-gray-700"></div>
                </div>
            </div>
        </div>

        <!-- Opções de Ação -->
        <div class="flex flex-col items-center space-y-4">
            <p class="text-gray-700">Precisa fazer alguma alteração no email?</p>
            <div class="flex space-x-4">
                <button onclick="showEditForm()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center">
                    <i class="material-icons mr-2">edit</i> Editar Email
                </button>
                <button onclick="confirmarEnvio()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center">
                    <i class="material-icons mr-2">send</i> Enviar Email
                </button>
                <button onclick="closeEmailModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 flex items-center">
                    <i class="material-icons mr-2">close</i> Cancelar
                </button>
            </div>
        </div>

        <!-- Formulário de Edição (inicialmente escondido) -->
        <div id="editForm" class="hidden mt-4 border-t pt-4">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-semibold">Editar Email</h4>
                <div class="flex gap-2">
                    <button onclick="updatePreview()" class="text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        <i class="material-icons">preview</i>
                        Ver Preview
                    </button>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email do Destinatário</label>
                    <input type="email" id="emailTo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Assunto</label>
                    <input type="text" id="emailSubject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Mensagem</label>
                    <div class="mt-1 bg-gray-50 p-2 rounded text-sm mb-2">
                        <span class="text-gray-600">Dicas:</span>
                        <ul class="list-disc ml-4 text-gray-500">
                            <li>Use * texto * para deixar em negrito (ex: *importante*)</li>
                            <li>Pressione Enter para criar novas linhas</li>
                        </ul>
                    </div>
                    <textarea id="emailContent" rows="8"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm font-sans"></textarea>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-end space-x-3 mt-4 pt-4 border-t">
            <button onclick="closeEmailModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                Cancelar
            </button>
            <button onclick="confirmarEnvio()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-2">
                <i class="material-icons">send</i>
                Enviar Email
            </button>
        </div>
    </div>
</div>

<!-- Script do Modal de Email -->
<script>
    // Não redeclarar as variáveis se já existirem
    if (typeof window.emailData === 'undefined') {
        window.emailData = null;
    }
    if (typeof window.isProcessing === 'undefined') {
        window.isProcessing = false;
    }

    function closeEmailModal() {
        const modal = document.getElementById('emailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function showEmailModal(data) {
        window.emailData = data;
        const modal = document.getElementById('emailModal');

        // Formatação do conteúdo para exibição amigável
        let contentHtml = data.conteudo;
        // Remove tags HTML mantendo quebras de linha
        let contentText = contentHtml.replace(/<br\s*\/?>/g, '\n')
            .replace(/<\/p>/g, '\n')
            .replace(/<[^>]*>/g, '')
            .replace(/\n\s*\n/g, '\n')
            .trim();

        // Preenche os campos com os dados
        document.getElementById('previewTo').textContent = data.email;
        document.getElementById('previewSubject').textContent = data.titulo;
        document.getElementById('emailTo').value = data.email;
        document.getElementById('emailSubject').value = data.titulo;
        document.getElementById('emailContent').value = contentText;

        // Preview inicial
        updatePreview();

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('editForm').classList.add('hidden');
    }

    function showEditForm() {
        document.getElementById('editForm').classList.remove('hidden');
    }

    function updatePreview() {
        const emailPreview = document.getElementById('emailPreview');
        const content = document.getElementById('emailContent').value;

        // Converte quebras de linha em <br> para o preview
        const formattedContent = content.replace(/\n/g, '<br>')
            .replace(/\*(.*?)\*/g, '<strong>$1</strong>'); // Suporte básico para markdown

        // Aplica a formatação base
        emailPreview.innerHTML = `
            <div style="font-family: Arial, sans-serif; line-height: 1.6;">
                ${formattedContent}
                <br><br>
                <div style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
                    <p style="color: #666; font-size: 14px;">
                        <strong>Canais de Atendimento DEMUTRAN:</strong><br>
                        📞 Telefone: (84) 3351-2868<br>
                        📧 E-mail: demutran@paudosferros.rn.gov.br<br>
                        📍 Endereço: Av. Getúlio Vargas, 1323, Centro, Pau dos Ferros-RN
                    </p>
                </div>
            </div>
        `;

        // Atualizar também o preview do cabeçalho
        document.getElementById('previewTo').textContent = document.getElementById('emailTo').value;
        document.getElementById('previewSubject').textContent = document.getElementById('emailSubject').value;
    }

    async function confirmarEnvio() {
        if (window.isProcessing) return;

        try {
            window.isProcessing = true;
            closeEmailModal();
            showModal('loading');

            const response = await fetch('./actions/concluir_formulario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: '<?php echo $_GET['id'] ?? ''; ?>',
                    tipo: '<?php echo $_GET['tipo'] ?? ''; ?>',
                    email: document.getElementById('emailTo').value,
                    assunto: document.getElementById('emailSubject').value,
                    conteudo: document.getElementById('emailContent').value,
                    confirmed: true
                })
            });

            const data = await response.json();

            if (data.success) {
                showModal('success');
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                document.getElementById('errorMessage').textContent = data.message;
                showModal('error');
            }
        } catch (error) {
            console.error('Erro:', error);
            document.getElementById('errorMessage').textContent = error.message;
            showModal('error');
        } finally {
            window.isProcessing = false;
        }
    }

    // Adiciona listeners quando o documento carrega
    document.addEventListener('DOMContentLoaded', function() {
        const emailModal = document.getElementById('emailModal');

        if (emailModal) {
            emailModal.addEventListener('click', function(e) {
                if (e.target === this && !window.isProcessing) closeEmailModal();
            });
        }
    });
</script>

<?php
// Esse arquivo é incluído no detalhes_formulario.php
// e pode conter scripts que usam as variáveis isProcessing e emailData
?>

<script>
    // Remover qualquer declaração duplicada destas variáveis
    // window.isProcessing e window.emailData já estão definidos no arquivo principal

    // Exemplo de função que usa as variáveis globais corretamente
    function enviarEmailProcessado() {
        if (window.isProcessing) return;

        try {
            window.isProcessing = true;

            // Lógica para processar o email usando window.emailData
            console.log('Processando email para:', window.emailData.email);

            // Restante do código...
        } catch (error) {
            console.error('Erro ao processar email:', error);
        } finally {
            window.isProcessing = false;
        }
    }
</script>