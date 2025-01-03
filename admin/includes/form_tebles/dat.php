<?php
function exibirDetalhesDAT($conn, $id)
{
    // Buscar formulário central
    $sql = "SELECT * FROM formularios_dat_central WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $formulario = $stmt->get_result()->fetch_assoc();

    if (!$formulario) {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>
                <p class='font-bold'>Formulário não encontrado</p>
                <p>Não foi possível encontrar o formulário com ID #$id.</p>
              </div>";
        return;
    }

    $token = $formulario['token'];

    // Exibir informações do formulário central
    echo "<div class='space-y-6'>";

    // Card do formulário central
    echo "<div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
            <h3 class='text-xl font-bold text-blue-600 mb-4 border-b pb-2'>Informações do Formulário Central</h3>
            <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";
    foreach ($formulario as $campo => $valor) {
        $campoFormatado = ucfirst(str_replace('_', ' ', $campo));
        echo "<div class='p-2 hover:bg-gray-50 rounded'>
                <span class='font-semibold text-gray-700'>$campoFormatado:</span>
                <span class='ml-2 text-gray-600'>" . formatarValor($campo, $valor) . "</span>
              </div>";
    }
    echo "</div></div>";

    // Buscar DAT1
    $sql = "SELECT * FROM DAT1 WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $dat1 = $stmt->get_result()->fetch_assoc();

    if ($dat1) {
        echo "<div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
                <h3 class='text-xl font-bold text-blue-600 mb-4 border-b pb-2'>Informações do Declarante (DAT1)</h3>
                <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";
        foreach ($dat1 as $campo => $valor) {
            $campoFormatado = ucfirst(str_replace('_', ' ', $campo));
            echo "<div class='p-2 hover:bg-gray-50 rounded'>
                    <span class='font-semibold text-gray-700'>$campoFormatado:</span>
                    <span class='ml-2 text-gray-600'>" . formatarValor($campo, $valor) . "</span>
                  </div>";
        }
        echo "</div></div>";
    }

    // Buscar DAT2
    $sql = "SELECT * FROM DAT2 WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $dat2Result = $stmt->get_result();

    if ($dat2Result->num_rows > 0) {
        echo "<div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
                <h3 class='text-xl font-bold text-blue-600 mb-4 border-b pb-2'>Informações dos Veículos (DAT2)</h3>
                <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";

        while ($dat2 = $dat2Result->fetch_assoc()) {
            foreach ($dat2 as $campo => $valor) {
                $campoFormatado = ucfirst(str_replace('_', ' ', $campo));
                echo "<div class='p-2 hover:bg-gray-50 rounded'>
                        <span class='font-semibold text-gray-700'>$campoFormatado:</span>
                        <span class='ml-2 text-gray-600'>" . formatarValor($campo, $valor) . "</span>
                      </div>";
            }
        }
        echo "</div></div>";
    }

    // Buscar DAT4
    $sql = "SELECT * FROM DAT4 WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $dat4 = $stmt->get_result()->fetch_assoc();

    if ($dat4) {
        echo "<div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
                <h3 class='text-xl font-bold text-blue-600 mb-4 border-b pb-2'>Informações Complementares (DAT4)</h3>
                <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";
        foreach ($dat4 as $campo => $valor) {
            $campoFormatado = ucfirst(str_replace('_', ' ', $campo));
            echo "<div class='p-2 hover:bg-gray-50 rounded'>
                    <span class='font-semibold text-gray-700'>$campoFormatado:</span>
                    <span class='ml-2 text-gray-600'>" . formatarValor($campo, $valor) . "</span>
                  </div>";
        }
        echo "</div></div>";
    }

    // Buscar veículos do usuário (que será apenas um registro por formulário)
    $sql = "SELECT uv.*, vd.* FROM user_vehicles uv 
            LEFT JOIN vehicle_damages vd ON vd.user_vehicles_id = uv.id 
            WHERE uv.token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
                <h3 class='text-xl font-bold text-blue-600 mb-4 border-b pb-2'>Veículos Registrados</h3>";

        $vehicleCount = 1;
        while ($row = $result->fetch_assoc()) {
            echo "<div class='mb-6 bg-blue-50 p-4 rounded-lg'>
                    <h4 class='font-bold text-lg text-blue-800 mb-3'>Veículo #" . $vehicleCount . "</h4>
                    <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>";

            // Exibir informações de danos do veículo
            foreach ($row as $campo => $valor) {
                // Pular campos que não queremos exibir
                if (in_array($campo, ['id', 'token', 'user_vehicles_id', 'data_submissao'])) continue;

                $campoFormatado = ucfirst(str_replace('_', ' ', $campo));
                echo "<div class='p-2 bg-white rounded shadow'>
                        <span class='font-semibold text-gray-700'>$campoFormatado:</span>
                        <span class='ml-2 text-gray-600'>" . formatarValor($campo, $valor) . "</span>
                      </div>";
            }

            echo "</div></div>";
            $vehicleCount++;
        }

        echo "</div>";
    }

    echo "</div>";
}

function formatarValor($campo, $valor)
{
    if ($valor === null || $valor === '') {
        return "<span class='text-gray-400 italic'>Não informado</span>";
    }

    // Formatar datas
    if (strpos(strtolower($campo), 'data') !== false && strtotime($valor)) {
        return date('d/m/Y', strtotime($valor));
    }

    // Formatar horários
    if (strpos(strtolower($campo), 'horario') !== false || strpos(strtolower($campo), 'hora') !== false) {
        return date('H:i', strtotime($valor));
    }

    // Formatar timestamps
    if (strpos(strtolower($campo), 'timestamp') !== false) {
        return date('d/m/Y H:i:s', strtotime($valor));
    }

    // Formatar valores booleanos
    if (is_numeric($valor) && ($valor == 0 || $valor == 1)) {
        return "<span class='px-2 py-1 rounded-full text-xs " .
        ($valor == 1 ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800") . "'>" .
            ($valor == 1 ? "Sim" : "Não") . "</span>";
    }

    // Formatar valores monetários
    if (strpos(strtolower($campo), 'valor') !== false && is_numeric($valor)) {
        return "R$ " . number_format($valor, 2, ',', '.');
    }

    // Formatar CPF
    if (strpos(strtolower($campo), 'cpf') !== false) {
        return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $valor);
    }

    return htmlspecialchars($valor);
}
?>