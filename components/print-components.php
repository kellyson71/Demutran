<?php
function renderPrintComponents()
{
    ob_start();
?>
    <style>
        .print-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: none;
            background: #1565C0;
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            min-width: 400px;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }

        .print-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .print-toast .toast-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .print-toast .icon-text {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
        }

        .print-toast .close-button {
            background: none;
            border: none;
            padding: 4px;
            color: white;
            opacity: 0.8;
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 4px;
        }

        .print-toast .close-button:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
        }

        .print-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9998;
            background-color: #2196F3;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .print-button:hover {
            background-color: #1976D2;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(33, 150, 243, 0.4);
        }

        .print-button svg {
            width: 20px;
            height: 20px;
        }

        @media print {

            .print-toast,
            .print-button {
                display: none !important;
            }
        }
    </style>

    <!-- Toast Notification -->
    <div id="print-toast" class="print-toast">
        <div class="toast-content">
            <div class="icon-text">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                    <path d="M6 14h12v8H6z" />
                </svg>
                <span>Pressione Ctrl + P ou clique no botão para imprimir</span>
            </div>
            <button onclick="hideToast()" class="close-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Botão de Impressão -->
    <button onclick="window.print()" class="print-button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
            <path d="M6 14h12v8H6z" />
        </svg>
        Imprimir formulário
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('print-toast');
            toast.style.display = 'block';

            setTimeout(() => {
                toast.classList.add('show');
            }, 100);

            setTimeout(() => {
                hideToast();
            }, 5000);
        });

        function hideToast() {
            const toast = document.getElementById('print-toast');
            toast.classList.remove('show');

            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
        }
    </script>
<?php
    return ob_get_clean();
}
?>