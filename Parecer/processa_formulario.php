<?php
// processa-solicitacao.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber os dados do formulário
    $nome_solicitante = $_POST['nome_solicitante'];
    $cpf_cnpj = $_POST['cpf_cnpj'];
    $telefone = $_POST['telefone'];
    $local = $_POST['local'];
    $evento = $_POST['evento'];
    $ponto_referencia = $_POST['ponto_referencia'];
    $data_horario = $_POST['data_horario'];

    // Aqui você pode inserir os dados no banco de dados ou enviar por e-mail
    // Por exemplo, enviar por e-mail:

    $to = 'email@demutran.paudosferros.gov.br'; // Substitua pelo e-mail do DEMUTRAN
    $subject = 'Nova Solicitação de Parecer';
    $message = "
    Nome do Solicitante: $nome_solicitante\n
    CPF/CNPJ: $cpf_cnpj\n
    Nº Telefone: $telefone\n
    Local: $local\n
    Evento: $evento\n
    Ponto de Referência: $ponto_referencia\n
    Data / Horário: $data_horario\n
    \n
    Venho por meio deste solicitar parecer ao Departamento Municipal de Trânsito – DEMUTRAN – Pau dos Ferros, quanto à viabilidade do evento acima citado no tocante ao trânsito no local. Informo ainda que me comprometo a colocar a sinalização que for necessária e determinada pelo DEMUTRAN.
    \n
    Pau dos Ferros – RN, " . date('d \d\e F \d\e Y') . "\n
    \n
    Atenciosamente,\n
    $nome_solicitante
    ";

    // Enviar o e-mail
    // mail($to, $subject, $message);

    // Exibir mensagem de sucesso
    echo "<h1>Solicitação Enviada com Sucesso!</h1>";
    echo "<p>Obrigado, $nome_solicitante. Sua solicitação foi recebida e será analisada pelo DEMUTRAN.</p>";
} else {
    // Redirecionar de volta se o acesso não for via POST
    header('Location: index.php');
}
?>
