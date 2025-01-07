<?php
require_once __DIR__ . '/helpers.php';

function exibirDetalhesSAC($conn, $id)
{
    $sql = "SELECT * FROM sac WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sac = $result->fetch_assoc();

    if (!$sac) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">Solicitação não encontrada.</span>
              </div>';
        return;
    }

    $dataSubmissao = new DateTime($sac['data_submissao']);
    $tipoContato = ucfirst($sac['tipo_contato']);
?>
<div class="bg-white shadow rounded-lg p-6">
    <!-- Informações do Solicitante -->
    <div class="border-b pb-6 mb-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informações do Solicitante</h3>
        <div class="space-y-4">
            <?php
                echo createEditableField('Nome', $sac['nome'], 'nome');
                echo createEditableField('Email', $sac['email'], 'email');
                echo createEditableField('Telefone', $sac['telefone'], 'telefone');
                ?>
        </div>
    </div>

    <!-- Detalhes da Solicitação -->
    <div class="border-b pb-6 mb-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Detalhes da <?php echo $tipoContato; ?></h3>
        <div class="space-y-4">
            <?php
                echo createEditableField('Assunto', $sac['assunto'], 'assunto');

                if ($sac['mensagem']): ?>
            <div>
                <p class="text-gray-600 font-semibold mb-2">Mensagem:</p>
                <?php echo createEditableField('Mensagem', $sac['mensagem'], 'mensagem'); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status da Solicitação -->
    <div>
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Status da <?php echo $tipoContato; ?></h3>
        <?php echo createEditableField('Status', $sac['situacao'], 'situacao'); ?>
    </div>
</div>
<?php
}
?>