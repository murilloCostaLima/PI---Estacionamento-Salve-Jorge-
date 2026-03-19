<?php

require_once("../model/cliente.php");
require_once("../model/veiculo.php");

$acao = $_POST['acao'] ?? '';

if ($acao == 'cadastrarCompleto') {

    // 🔹 DADOS CLIENTE
    $nome = $_POST['nomeCliente'];
    $telefone = $_POST['telefone'];
    $tipoCliente = $_POST['tipoCliente'];
    $bairro = $_POST['bairro'];
    $endereco = $_POST['endereco'];

    // salva cliente
    $cliente_id = cliente::cadastrar($nome, $telefone, $tipoCliente, $bairro, $endereco);

    // 🔹 DADOS VEÍCULO
    $tipoVeiculo = $_POST['tipoVeiculo'];
    $cor = $_POST['cor'];
    $placa = $_POST['placa'];
    $vaga = $_POST['vaga'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];

    // REGRA DE NEGÓCIO
    if ($vaga >= 85 && $vaga <= 90 && $tipoVeiculo != 'moto') {
        die("Erro: vagas 85–90 são exclusivas para motos.");
    }

    veiculo::cadastrar($cliente_id, $tipoVeiculo, $cor, $placa, $vaga, $marca, $modelo);

    header("Location: ../view/PainelCliente.php?sucesso=1");
    exit;
}