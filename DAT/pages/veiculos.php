<?php
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../../env/config.php');

// Array com as partes do veículo que podem ser danificadas
$partesDanificadas = [
  'dianteira_direita' => 'Dianteira Direita',
  'dianteira_esquerda' => 'Dianteira Esquerda',
  'lateral_direita' => 'Lateral/Teto Direito',
  'lateral_esquerda' => 'Lateral/Teto Esquerdo',
  'traseira_direita' => 'Traseira Direita',
  'traseira_esquerda' => 'Traseira Esquerda'
];

// Verifica se é uma requisição AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
  // Se for AJAX, apenas renderiza o formulário do veículo
  $index = isset($_GET['index']) ? (int)$_GET['index'] : 1;
  echo renderVehicleForm($index, $partesDanificadas);
  exit;
}

// Verifica token apenas se não for requisição AJAX
$token = isset($_GET['token']) ? $_GET['token'] : null;
if (!$token) {
  header('Location: termos.php');
  exit;
}

// Função para gerar o HTML de um veículo
function renderVehicleForm($index, $partesDanificadas)
{
  ob_start();
?>
  <div class="vehicle-form dat-vehicle-section bg-white p-6 rounded-xl shadow-md mb-6 border border-gray-200"
    data-vehicle="<?= $index ?>">
    <!-- Cabeçalho do Veículo -->
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
      <div class="flex items-center gap-3">
        <div class="bg-green-100 p-2 rounded-lg">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
            </path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-800">Veículo <?= $index ?></h3>
      </div>
      <button type="button"
        class="toggle-vehicle-details text-sm font-medium text-green-600 hover:text-green-700 flex items-center gap-2">
        <span>Minimizar</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>
    </div>

    <div class="vehicle-details space-y-6">
      <!-- Seção de Danos -->
      <div class="damage-section bg-gray-50 p-4 rounded-lg">
        <div class="mb-4">
          <label class="inline-flex items-center">
            <input type="checkbox" name="damage_system_<?= $index ?>"
              class="form-checkbox h-5 w-5 text-green-600 rounded damage-checkbox">
            <span class="ml-2 text-gray-700">Houve danos ao sistema de segurança, freios, direção ou
              suspensão?</span>
          </label>
        </div>

        <div class="damage-parts hidden mt-4">
          <h4 class="font-medium text-gray-700 mb-3">Selecione as partes danificadas:</h4>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <?php foreach ($partesDanificadas as $key => $label): ?>
              <label
                class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-green-500 transition-colors cursor-pointer">
                <input type="checkbox" name="parte_danificada_<?= $key ?>_<?= $index ?>"
                  class="form-checkbox h-4 w-4 text-green-600 rounded">
                <span class="ml-2 text-gray-600"><?= $label ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Seção de Carga -->
      <div class="cargo-section bg-gray-50 p-4 rounded-lg">
        <div class="mb-4">
          <label class="inline-flex items-center">
            <input type="checkbox" name="load_damage_<?= $index ?>"
              class="form-checkbox h-5 w-5 text-green-600 rounded load-damage-checkbox">
            <span class="ml-2 text-gray-700">Houve danos na carga do veículo?</span>
          </label>
        </div>

        <div class="load-damage-info hidden space-y-4">
          <!-- Campos de informação da carga -->
          <?php
          $cargoFields = [
            'nota_fiscal' => 'Nº das Notas Fiscais, Manifestos ou Equivalentes',
            'tipo_mercadoria' => 'Tipo de Mercadoria',
            'valor_total' => 'Valor Total',
            'estimativa_danos' => 'Extensão estimada dos danos'
          ];

          foreach ($cargoFields as $field => $label):
          ?>
            <div class="form-group">
              <label class="block text-gray-700 mb-2"><?= $label ?>:</label>
              <input type="text" name="<?= $field ?>_<?= $index ?>"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
            </div>
          <?php endforeach; ?>

          <!-- Seção de Seguro -->
          <div class="insurance-section mt-4">
            <label class="inline-flex items-center mb-4">
              <input type="checkbox" name="has_insurance_<?= $index ?>"
                class="form-checkbox h-5 w-5 text-green-600 rounded load-insurance-checkbox">
              <span class="ml-2 text-gray-700">A carga possui seguro?</span>
            </label>

            <div class="load-insurance-info hidden">
              <div class="form-group">
                <label class="block text-gray-700 mb-2">Nome da Seguradora:</label>
                <input type="text" name="seguradora_<?= $index ?>"
                  class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php
  return ob_get_clean();
}
?>

<!-- Adicionar estilos necessários no topo -->
<style>
  .form-checkbox {
    appearance: none;
    padding: 0;
    print-color-adjust: exact;
    display: inline-block;
    vertical-align: middle;
    background-origin: border-box;
    user-select: none;
    flex-shrink: 0;
    height: 1.25rem;
    width: 1.25rem;
    border: 2px solid #d1d5db;
    border-radius: 0.375rem;
  }

  .form-checkbox:checked {
    background-color: #10b981;
    border-color: #10b981;
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
  }

  .form-checkbox:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #10b981;
  }

  /* Ajuste da altura dos inputs */
  input[type="text"],
  input[type="number"],
  input[type="email"],
  .form-group input {
    padding-top: 0.625rem !important;
    /* 10px */
    padding-bottom: 0.625rem !important;
    /* 10px */
    height: 2.75rem !important;
    /* 44px */
  }

  /* Ajuste do checkbox para alinhar melhor com o texto */
  .form-checkbox {
    margin-top: 0.125rem !important;
  }
</style>

<div class="pt-20 flex justify-center flex-col items-center bg-gray-50 min-h-screen">
  <div class="container mx-auto px-4 max-w-4xl">
    <?php require_once(__DIR__ . '/../includes/progresso.php'); ?>

    <div class="bg-white p-8 rounded-xl shadow-lg">
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Cadastro de Veículos Envolvidos</h2>
        <p class="text-gray-600">Para começar, clique no botão "Adicionar Veículo" abaixo</p>
      </div>

      <form id="form-veiculo" class="space-y-6">
        <div id="vehicle-container"
          class="empty-state flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
          <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          <p class="text-gray-500">Nenhum veículo adicionado</p>
        </div>

        <div class="flex gap-4 justify-center mt-8">
          <button type="button" id="add-vehicle-btn"
            class="flex items-center gap-2 px-6 py-3 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Adicionar Veículo
          </button>

          <button type="button" id="submit-data-btn"
            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            disabled>
            Avançar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  let vehicleCount = 0;

  // Função para habilitar/desabilitar o botão de avançar
  function toggleSubmitButton() {
    const submitBtn = document.getElementById('submit-data-btn');
    const emptyState = document.querySelector('.empty-state');

    submitBtn.disabled = vehicleCount === 0;
    if (vehicleCount > 0) {
      emptyState?.remove();
    }
  }

  // Remover o carregamento automático
  // document.addEventListener('DOMContentLoaded', function() {
  //   document.getElementById('add-vehicle-btn').click();
  // });

  function addVehicle() {
    vehicleCount++;
    const container = document.getElementById('vehicle-container');

    // Remove o estado vazio se for o primeiro veículo
    if (vehicleCount === 1) {
      container.classList.remove('empty-state', 'flex', 'flex-col', 'items-center', 'justify-center', 'p-8',
        'bg-gray-50', 'border-2', 'border-dashed', 'border-gray-300');
      container.innerHTML = '';
    }

    // Faz a requisição para o mesmo arquivo
    fetch(window.location.href + '?index=' + vehicleCount, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.text())
      .then(html => {
        container.insertAdjacentHTML('beforeend', html);
        const newVehicle = container.lastElementChild;

        // Força a aplicação dos estilos
        newVehicle.querySelectorAll('a').forEach(link => {
          link.style.color = 'inherit';
          link.style.textDecoration = 'none';
        });

        initializeVehicleForm(newVehicle);
        toggleSubmitButton();
      });
  }

  document.getElementById('add-vehicle-btn').addEventListener('click', addVehicle);

  function initializeVehicleForm(form) {
    // Toggle visibility
    const toggleBtn = form.querySelector('.toggle-vehicle-details');
    const detailsDiv = form.querySelector('.vehicle-details');

    toggleBtn.addEventListener('click', () => {
      const isHidden = detailsDiv.classList.contains('hidden');
      detailsDiv.classList.toggle('hidden');

      // Atualiza o texto e ícone do botão
      const span = toggleBtn.querySelector('span');
      const svg = toggleBtn.querySelector('svg');

      span.textContent = isHidden ? 'Minimizar' : 'Expandir';
      svg.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
    });

    // Checkboxes interativos
    const damageCheckbox = form.querySelector('.damage-checkbox');
    const damagePartsDiv = form.querySelector('.damage-parts');

    damageCheckbox?.addEventListener('change', () => {
      damagePartsDiv.classList.toggle('hidden', !damageCheckbox.checked);
    });

    const loadDamageCheckbox = form.querySelector('.load-damage-checkbox');
    const loadDamageInfoDiv = form.querySelector('.load-damage-info');

    loadDamageCheckbox?.addEventListener('change', () => {
      loadDamageInfoDiv.classList.toggle('hidden', !loadDamageCheckbox.checked);
    });

    const loadInsuranceCheckbox = form.querySelector('.load-insurance-checkbox');
    const loadInsuranceInfoDiv = form.querySelector('.load-insurance-info');

    loadInsuranceCheckbox?.addEventListener('change', () => {
      loadInsuranceInfoDiv.classList.toggle('hidden', !loadInsuranceCheckbox.checked);
    });
  }

  // Inicializa todos os formulários existentes
  document.querySelectorAll('.vehicle-form').forEach(initializeVehicleForm);

  // Adiciona handler para o formulário
  document.getElementById('form-veiculo').addEventListener('submit', function(e) {
    e.preventDefault();
    // ...existing submit logic...
  });

  // Adicionar lógica do botão avançar
  document.getElementById('submit-data-btn').addEventListener('click', function() {
    const vehicleForms = document.querySelectorAll('.vehicle-form');
    const vehiclesData = [];
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    // Coletar dados de cada veículo
    vehicleForms.forEach((form, index) => {
      const vehicleData = {
        damageSystem: form.querySelector(`[name="damage_system_${index + 1}"]`).checked,
        damagedParts: [],
        loadDamage: form.querySelector(`[name="load_damage_${index + 1}"]`).checked
      };

      // Coletar partes danificadas
      const partCheckboxes = form.querySelectorAll('[name^="parte_danificada_"]');
      partCheckboxes.forEach(checkbox => {
        vehicleData.damagedParts.push({
          name: checkbox.name,
          checked: checkbox.checked
        });
      });

      // Coletar informações de carga se houver danos
      if (vehicleData.loadDamage) {
        vehicleData.notaFiscal = form.querySelector(`[name="nota_fiscal_${index + 1}"]`).value;
        vehicleData.tipoMercadoria = form.querySelector(`[name="tipo_mercadoria_${index + 1}"]`)
          .value;
        vehicleData.valorTotal = form.querySelector(`[name="valor_total_${index + 1}"]`).value;
        vehicleData.estimativaDanos = form.querySelector(`[name="estimativa_danos_${index + 1}"]`)
          .value;
        vehicleData.hasInsurance = form.querySelector(`[name="has_insurance_${index + 1}"]`)
          .checked;

        if (vehicleData.hasInsurance) {
          vehicleData.seguradora = form.querySelector(`[name="seguradora_${index + 1}"]`).value;
        }
      }

      vehiclesData.push(vehicleData);
    });

    // Enviar dados para o servidor
    fetch('../Process_form/DAT3.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          vehiclesData,
          token
        })
      })
      .then(response => response.json())
      .then(data => {
        console.log('Resposta do servidor:', data);
        if (data.success) {
          window.location.href = `revisao.php?token=${token}`; // Redireciona para próxima página
        } else {
          alert(data.error || 'Erro ao salvar os dados');
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
      });
  });
</script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>