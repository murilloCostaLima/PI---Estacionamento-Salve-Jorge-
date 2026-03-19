<?php

require_once("../config/conexao.php");
require_once("../model/cliente.php");
require_once("../model/veiculo.php");

$acao = $_POST['acao'] ?? '';

// ===============================
// CADASTRAR CLIENTE + VEÍCULO
// ===============================
if ($acao === 'cadastrarCompleto') {

    // ========= DADOS DO CLIENTE =========
    $nome         = $_POST['nomeCliente'] ?? null;
    $telefone     = $_POST['telefone'] ?? null;
    $tipo_cliente = $_POST['tipoCliente'] ?? null;
    $bairro       = $_POST['bairro'] ?? null;
    $endereco     = $_POST['endereco'] ?? null;

    // ========= DADOS DO VEÍCULO =========
    $tipo_veiculo = $_POST['tipoVeiculo'] ?? null;
    $cor          = $_POST['cor'] ?? null;
    $placa        = $_POST['placa'] ?? null;
    $vaga         = $_POST['vaga'] ?? null;
    $marca        = $_POST['marca'] ?? null;
    $modelo       = $_POST['modelo'] ?? null;

    try {
        // Conexão e transação
        $pdo = (new Conexao())->conexao();
        $pdo->beginTransaction();

        // ===== INSERIR CLIENTE =====
        $id_cliente = cliente::inserir(
            $nome,
            $telefone,
            $endereco,
            $bairro,
            $tipo_cliente
        );

        // ===== INSERIR VEÍCULO =====
        veiculo::inserir(
            (int)$vaga,
            (int)$id_cliente,
            $placa,
            $cor,
            $marca,
            $modelo,
            $tipo_veiculo
        );

        // Confirma tudo
        $pdo->commit();

        header("Location: ../view/ViewPainel.php?sucesso=1");
        exit;

    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die("Erro no cadastro completo: " . $e->getMessage());
    }
}

// ===============================
// CADASTRAR APENAS VEÍCULO
// ===============================
if ($acao === 'cadastrarVeiculo') {

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
            $tipo_veiculo
        );

        $pdo->commit();

        header("Location: ../view/ViewPainel.php?sucesso=1");
        exit;

    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die("Erro ao cadastrar veículo: " . $e->getMessage());
    }
}