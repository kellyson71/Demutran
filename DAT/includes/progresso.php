<?php
$currentStep = isset($currentStep) ? $currentStep : 0;
$progressWidth = $currentStep * 16.666667;
?>

<div class="bg-white p-8 rounded-lg shadow-lg w-full mb-4">
    <div class="relative w-full bg-gray-200 rounded-full h-2 progress-container">
        <div id="progress-bar" class="absolute top-0 h-2 bg-green-500 rounded-full progress-bar" 
             style="width: <?php echo $progressWidth; ?>%"></div>
    </div>
    <div class="flex justify-between text-sm text-gray-600 mt-2">
        <div class="<?php echo $currentStep >= 1 ? 'text-green-500 font-semibold' : ''; ?>">Termos</div>
        <div class="<?php echo $currentStep >= 2 ? 'text-green-500 font-semibold' : ''; ?>">Verificação</div>
        <div class="<?php echo $currentStep >= 3 ? 'text-green-500 font-semibold' : ''; ?>">Dados Gerais</div>
        <div class="<?php echo $currentStep >= 4 ? 'text-green-500 font-semibold' : ''; ?>">Veículo e Condutor</div>
        <div class="<?php echo $currentStep >= 5 ? 'text-green-500 font-semibold' : ''; ?>">Envolvidos</div>
        <div class="<?php echo $currentStep >= 6 ? 'text-green-500 font-semibold' : ''; ?>">Narrativa</div>
    </div>
</div>