</div> <!-- Fechamento do container principal -->
</div> <!-- Fechamento do flex container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Funções comuns
function nextStep(step) {
    document.querySelectorAll(".step").forEach(function(stepDiv) {
        stepDiv.classList.add("hidden");
    });
    document.getElementById("step-" + step).classList.remove("hidden");
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

    const progressBar = document.getElementById("progress-bar");
    const width = (step - 1) * 16.6666667;
    progressBar.style.width = `${width}%`;

    if (step === 6) {
        fetchTableData();
    }
}

function showToast(step) {
    const toast = document.getElementById('toast-progress');
    const message = document.getElementById('toast-message');
    message.textContent = `Progresso restaurado para a etapa ${step}`;
    toast.classList.remove('hidden');

    setTimeout(() => {
        closeToast();
    }, 3000);
}

function closeToast() {
    const toast = document.getElementById('toast-progress');
    toast.classList.add('hidden');
}

function getTokenFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('token');
}
</script>
</body>

</html>