document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contatoForm");
    form.addEventListener("submit", function(event) {
        event.preventDefault();
        submitForm();
    });
});

function submitForm() {
    const form = document.getElementById("contatoForm");
    const formData = new FormData(form);

    fetch("processa_formulario.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        const feedback = document.getElementById("feedback");
        feedback.innerHTML = data;
        feedback.classList.add("success");
        feedback.classList.remove("error");
        feedback.style.display = "block";
        
        // Ocultar mensagem após 2 segundos
        setTimeout(() => {
            feedback.style.display = "none";
        }, 2000);
    })
    .catch(error => {
        const feedback = document.getElementById("feedback");
        feedback.innerHTML = "Ocorreu um erro ao enviar o formulário.";
        feedback.classList.add("error");
        feedback.classList.remove("success");
        feedback.style.display = "block";
        
        // Ocultar mensagem após 3 segundos
        setTimeout(() => {
            feedback.style.display = "none";
        }, 3000);

        console.error("Erro:", error);
    });
}
