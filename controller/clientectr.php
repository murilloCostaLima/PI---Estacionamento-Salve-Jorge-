<?php

session_start();

require_once("../config/conexao.php");
require_once("../model/cliente.php");
require_once("../model/veiculo.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/ViewPainel.php");
    exit;
}

$acao = $_POST['acao'] ?? '';

// ===============================
// CADASTRAR CLIENTE + VEÍCULO
// ===============================
if ($acao === 'cadastrarCompleto') {

    // CLIENTE
    $nome         = $_POST['nomeCliente'];
    $telefone     = $_POST['telefone'];
    $tipo_cliente = $_POST['tipoCliente'];
    $bairro       = $_POST['bairro'];
    $endereco     = $_POST['endereco'];

    // VEÍCULO
    $tipo_veiculo = $_POST['tipoVeiculo'];
    $cor          = $_POST['cor'];
    $placa        = $_POST['placa'];
    $vaga         = (int)$_POST['vaga'];
    $marca        = $_POST['marca'];
    $modelo       = $_POST['modelo'];

    try {
        $pdo = (new Conexao())->conexao();
        $pdo->beginTransaction();

        $id_cliente = cliente::inserirComPDO(
            $pdo,
            $nome,
            $telefone,
            $endereco,
            $bairro,
            $tipo_cliente
        );

        veiculo::inserirComPDO(
            $pdo,
            $vaga,
            $id_cliente,
            $placa,
            $cor,
            $marca,
            $modelo,
            $tipo_veiculo
        );

        $pdo->commit();

        $_SESSION['success'] = "Cliente e veículo cadastrados com sucesso!";
        $_SESSION['flash_from'] = 'cadastrarCompleto';
        header("Location: ../view/PainelCliente.php");
        exit;
    } catch (Throwable $e) {



        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $msg = $e->getMessage();

        if (
            str_contains($msg, 'uk_veiculo_placa')
            || (str_contains($msg, 'Duplicate entry') && str_contains($msg, 'placa'))
        ) {
            $_SESSION['error'] = "Já existe um veículo cadastrado com esta placa.";
        } elseif (
            str_contains($msg, 'uk_cliente_telefone')
            || (str_contains($msg, 'Duplicate entry') && str_contains($msg, 'telefone'))
        ) {
            $_SESSION['error'] = "Já existe um cliente cadastrado com este telefone.";
        } else {
            $_SESSION['error'] = $msg;
        }

        $_SESSION['flash_from'] = 'cadastrarCompleto';
        header("Location: ../view/PainelCliente.php");
        exit;
    }
}

// ===============================
// CADASTRAR APENAS VEÍCULO
// (cliente já existente)
// ===============================
if ($acao === 'cadastrarVeiculo') {

    $id_cliente   = (int)($_POST['tipoCliente'] ?? 0);
    $tipo_veiculo = $_POST['tipoVeiculo'];
    $cor          = $_POST['cor'];
    $placa        = $_POST['placa'];
    $vaga         = (int)$_POST['vaga'];
    $marca        = $_POST['marca'];
    $modelo       = $_POST['modelo'];

    if ($id_cliente <= 0) {
        $_SESSION['error'] = "Cliente inválido.";
        header("Location: ../view/PainelVeiculo.php");
        exit;
    }

    try {
        $pdo = (new Conexao())->conexao();
        $pdo->beginTransaction();

        veiculo::inserirComPDO(
            $pdo,
            $vaga,
            $id_cliente,
            $placa,
            $cor,
            $marca,
            $modelo,
            $tipo_veiculo
        );

        $pdo->commit();

        $_SESSION['success'] = "Veículo cadastrado com sucesso!";
        $_SESSION['flash_from'] = 'cadastrarVeiculo';
        header("Location: ../view/PainelVeiculo.php");
        exit;
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // TRATAMENTO DE PLACA DUPLICADA
        if (
            str_contains($e->getMessage(), 'uk_veiculo_placa')
            || str_contains($e->getMessage(), 'Duplicate entry')
        ) {

            $_SESSION['error'] = "Já existe um veículo cadastrado com esta placa.";
        } else {
            $_SESSION['error'] = $e->getMessage();
        }

        $_SESSION['flash_from'] = 'cadastrarVeiculo';
        header("Location: ../view/PainelVeiculo.php");
        exit;
    }
}

// ===============================
// EDITAR CLIENTE + VEÍCULO
// ===============================
if ($acao === 'editarCompleto') {

    $id_veiculo = (int)$_POST['id_veiculo'];
    $id_cliente = (int)$_POST['id_cliente'];

    try {
        $pdo = (new Conexao())->conexao();
        $pdo->beginTransaction();

        // ✅ Atualizar cliente
        $cliente = cliente::buscarPorID($id_cliente);
        $cliente->nome = $_POST['nomeCliente'];
        $cliente->telefone = $_POST['telefone'];
        $cliente->bairro = $_POST['bairro'];
        $cliente->endereco = $_POST['endereco'];
        $cliente->tipo_cliente = ucfirst(strtolower($_POST['tipoCliente']));
        $cliente->atualizar();

        // ✅ Atualizar veículo
        veiculo::atualizar(
            $id_veiculo,
            null,
            $id_cliente,
            $_POST['placa'],
            $_POST['cor'],
            $_POST['marca'],
            $_POST['modelo'],
            $_POST['tipoVeiculo']
        );

        $pdo->commit();

        $_SESSION['success'] = "Registro atualizado com sucesso!";
        header("Location: ../view/ViewPainel.php");
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../view/PainelCliente.php?id=$id_veiculo");
        exit;
    }
}

// ===============================
// AÇÃO INVÁLIDA
// ===============================
// $_SESSION['error'] = "Ação inválida.";
// header("Location: ../view/ViewPainel.php");
// exit;
