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
    $cliente_id = cliente::inserir($nome, $telefone, $endereco, $bairro, $tipo_cliente);

    // 🔹 DADOS VEÍCULO
    $tipo_veiculo = $_POST['tipoVeiculo'];
    $cor = $_POST['cor'];
    $placa = $_POST['placa'];
    $vaga = $_POST['vaga'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];

    // REGRA DE NEGÓCIO
    if ($vaga >= 85 && $vaga <= 90 && $tipo_veiculo != 'moto') {
        die("Erro: vagas 85–90 são exclusivas para motos.");
    }

    veiculo::inserir($veiculo_id, $cor, $placa, $vaga, $marca, $modelo, $tipo_veiculo);

    header("Location: ../view/PainelCliente.php?sucesso=1");
    exit;
}

if ($acao == 'cadastrarVeiculo') {

    $id_cliente = $_POST['tipoCliente']; // aqui é o ID do cliente
    $tipo_veiculo = $_POST['tipoVeiculo'];
    $cor = $_POST['cor'];
    $placa = $_POST['placa'];
    $vaga = $_POST['vaga'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];

    // REGRA
    if ($vaga >= 85 && $vaga <= 90 && $tipoVeiculo != 'moto') {
        die("Erro: vagas 85–90 são exclusivas para motos.");
    }

    veiculo::inserir($id_veiculo, $cor, $placa, $vaga, $marca, $modelo, $tipo_veiculo);

    header("Location: ../view/PainelVeiculo.php?sucesso=1");
    exit;
}