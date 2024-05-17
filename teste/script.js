const form = document.querySelector('.google-form-container form');
const inputs = document.querySelectorAll('.google-form-container .input-field input, .google-form-container .input-field textarea');

form.addEventListener('submit', (event) => {
  event.preventDefault();

  // Lógica para validação e envio do formulário (exemplo)
  if (validateForm()) {
    // Enviar dados do formulário (AJAX, fetch, etc.)
    alert('Formulário enviado com sucesso!');
    form.reset();
  }
});

function validateForm() {
  let isValid = true;
  inputs.forEach(input => {
    if (input.value.trim() === '') {
      input.classList.add('error');
      isValid = false;
    } else {
      input.classList.remove('error');
    }
  });
  return isValid;
}
