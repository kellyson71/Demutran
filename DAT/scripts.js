function toggleForeignFields() {
    const isForeigner = document.getElementById('foreigner').checked;
    const foreignFields = ['documentType', 'documentNumber', 'country'];

    foreignFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        field.disabled = !isForeigner;
        field.required = isForeigner;
        field.parentElement.classList.toggle('foreign-only', !isForeigner);
    });
}

function validateForm() {
    const form = document.getElementById('accidentForm');
    if (form.checkValidity()) {
        alert('Formulário enviado com sucesso!');
        form.submit();
    } else {
        alert('Por favor, preencha todos os campos obrigatórios.');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    toggleForeignFields();
});
