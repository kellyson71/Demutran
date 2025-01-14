document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("contatoForm");
  form.addEventListener("submit", function (event) {
    event.preventDefault();
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = "Enviando...";
    submitForm(submitButton);
  });
});

function submitForm(submitButton) {
  const form = document.getElementById("contatoForm");
  const formData = new FormData(form);

  fetch("processa_contato.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((data) => {
      const feedback = document.getElementById("feedback");
      feedback.innerHTML = "Formulário enviado com sucesso!";
      feedback.classList.add("success");
      feedback.classList.remove("error");
      feedback.style.display = "block";

      // Redirecionar após 2 segundos
      setTimeout(() => {
        window.location.href = "index.html";
      }, 2000);
    })
    .catch((error) => {
      const feedback = document.getElementById("feedback");
      feedback.innerHTML = "Ocorreu um erro ao enviar o formulário.";
      feedback.classList.add("error");
      feedback.classList.remove("success");
      feedback.style.display = "block";

      // Reabilitar o botão em caso de erro
      submitButton.disabled = false;
      submitButton.textContent = "Enviar";

      // Ocultar mensagem após 3 segundos
      setTimeout(() => {
        feedback.style.display = "none";
      }, 3000);

      console.error("Erro:", error);
    });
}
