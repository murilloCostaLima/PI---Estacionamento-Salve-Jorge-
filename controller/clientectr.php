<?php

require_once("../config/conexao.php");
require_once("../model/cliente.php");
require_once("../model/veiculo.php");

$acao = $_POST['acao'] ?? '';

// ===============================
// CADASTRAR CLIENTE + VEÍCULO
// ===============================
session_start();

require_once("../config/conexao.php");
require_once("../model/cliente.php");
require_once("../model/veiculo.php");

if ($_POST['acao'] !== 'cadastrarCompleto') {
    header("Location: ../view/PainelCliente.php");
    exit;
}

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

    // ✅ cliente só entra se veículo entrar
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
    header("Location: ../view/PainelCliente.php");
    exit;
} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['error'] = $e->getMessage();
    header("Location: ../view/PainelCliente.php");
    exit;
}

// ===============================
// CADASTRAR APENAS VEÍCULO
// ===============================
if ($acao === 'cadastrarVeiculo')
{

    $id_cliente   = $_POST['tipoCliente'] ?? null;
    $tipo_veiculo = $_POST['tipoVeiculo'] ?? null;
    $cor          = $_POST['cor'] ?? null;
    $placa        = $_POST['placa'] ?? null;
    $vaga         = $_POST['vaga'] ?? null;
    $marca        = $_POST['marca'] ?? null;
    $modelo       = $_POST['modelo'] ?? null;

    try {
        $pdo = (new Conexao())->conexao();
        $pdo->beginTransaction();

        veiculo::inserir(
            (int)$vaga,
           (int)$id_cliente,
            $placa,
            $cor,
            $marca,
            $modelo,
            $tipo_veiculo);

        header("Location: ../view/ViewPainel.php?sucesso=1");
        exit;
    }
    catch (Throwable $e)
    {
        if (isset($pdo) && $pdo->inTransaction())
        {
            $pdo->rollBack();
        }

        die("Erro ao cadastrar veículo: " . $e->getMessage());
    }
        if (isset($pdo) && $pdo->inTransaction())
        {
            $pdo->rollBack();
        }

        die("Erro ao cadastrar veículo: " . $e->getMessage());
}