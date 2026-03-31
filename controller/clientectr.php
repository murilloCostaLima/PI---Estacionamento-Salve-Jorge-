<?php
session_start();

require_once("../config/conexao.php");
require_once("../model/cliente.php");
require_once("../model/veiculo.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

/* ========================
EDITAR CLIENTE + VEÍCULO
======================== */
if (isset($_POST['acao']) && $_POST['acao'] === 'editarCompleto') {

    $id_cliente = (int)$_POST['id_cliente'];
    $id_veiculo = (int)$_POST['id_veiculo'];

    try {
        $pdo = (new Conexao())->conexao();
        $pdo->beginTransaction();

        // Buscar veículo atual
        $veiculoAtual = veiculo::buscarPorIDComPDO($pdo, $id_veiculo);
        if (!$veiculoAtual) {
            throw new Exception("Veículo não encontrado.");
        }

        /* ================
        Atualizar CLIENTE
        =================*/
        $cliente = cliente::buscarPorID($id_cliente);
        if (!$cliente) {
            throw new Exception("Cliente não encontrado.");
        }

        $cliente->nome         = $_POST['nomeCliente'];
        $cliente->telefone     = $_POST['telefone'];
        $cliente->bairro       = $_POST['bairro'];
        $cliente->endereco     = $_POST['endereco'];
        $cliente->tipo_cliente = ucfirst(strtolower($_POST['tipoCliente']));
        $cliente->atualizar();

        // Se cliente foi DESATIVADO, liberar todas as vagas ocupadas por ele
        if ($cliente->tipo_cliente === 'Desativado') {

            // Buscar veículos ATIVOS do cliente
            $stmt = $pdo->prepare("SELECT id_veiculo, id_vaga FROM veiculo WHERE id_cliente = :id AND hr_saida IS NULL");
            $stmt->execute([':id' => $id_cliente]);
            $veiculosAtivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($veiculosAtivos as $v) {
                // Finaliza o veículo
                $stmt = $pdo->prepare("UPDATE veiculo SET hr_saida = NOW() WHERE id_veiculo = :id");

                $stmt->execute([':id' => $v['id_veiculo']]);

                // Libera a vaga
                if ($v['id_vaga']) {
                    $stmt = $pdo->prepare("UPDATE vaga SET disponibilidade = 'disponivel' WHERE id_vaga = :id");
                    $stmt->execute([':id' => $v['id_vaga']]);
                }
            }
            // Se cliente foi desativado, NÃO processar vaga nem veículo
            if ($cliente->tipo_cliente === 'Desativado')
            {
                $pdo->commit();

                $_SESSION['mensagem'] = "Cliente desativado e vagas liberadas com sucesso.";
                $_SESSION['tipo_alerta'] = "success";

                header("Location: ../view/ViewPainel.php");
                exit;
            }
        }

        /* ===============================
        Processar TROCA DE VAGA (CORRETO)
        =============================== */

        $codigoVagaNova   = (int)$_POST['vaga'];                        // vaga digitada no formulário
        $codigoVagaAtual  = $veiculoAtual->vaga['codigo_vaga'] ?? null; // vaga atual do veículo
        $idVagaAntiga     = $veiculoAtual->id_vaga;                     // PK da vaga antiga

        // Só processa se a vaga realmente mudou
        if ($codigoVagaNova > 0 && $codigoVagaNova !== $codigoVagaAtual) {
            // LIBERAR VAGA ANTIGA (usa id_vaga)
            if ($idVagaAntiga) {
                $stmt = $pdo->prepare("UPDATE vaga SET disponibilidade = 'disponivel' WHERE id_vaga = :id");

                $stmt->execute([':id' => $idVagaAntiga]);
            }

            // BUSCAR NOVA VAGA DISPONÍVEL (usa codigo_vaga)
            $stmt = $pdo->prepare("
                SELECT id_vaga FROM vaga
                WHERE codigo_vaga = :codigo
                AND disponibilidade = 'disponivel'
                FOR UPDATE");

            $stmt->execute([':codigo' => $codigoVagaNova]);
            $idVagaNova = $stmt->fetchColumn();

            if (!$idVagaNova) {
                throw new Exception("A nova vaga não está disponível.");
            }

            // ✅ 3. OCUPAR NOVA VAGA (usa id_vaga)
            $stmt = $pdo->prepare("UPDATE vaga SET disponibilidade = 'ocupada' WHERE id_vaga = :id");

            $stmt->execute([':id' => $idVagaNova]);
        } else {
            // Nenhuma troca de vaga
            $idVagaNova = $idVagaAntiga;
        }

        /* =============================
        Atualizar VEÍCULO
        (placa e tipo são imutáveis)
        ============================= */
        veiculo::atualizarComPDO(
            $pdo,
            $id_veiculo,
            $idVagaNova,
            $id_cliente,
            $veiculoAtual->placa,
            $_POST['cor'],
            $_POST['marca'],
            $_POST['modelo'],
            $veiculoAtual->tipo_veiculo
        );

        $pdo->commit();

        // ✅ VOLTA PARA O PAINEL (COMO VOCÊ PEDIU)
        $_SESSION['mensagem'] = "Registro atualizado com sucesso!";
        $_SESSION['tipo_alerta'] = "success";
        header("Location: ../view/ViewPainel.php");
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['mensagem'] = $e->getMessage();
        $_SESSION['tipo_alerta'] = "danger";
        header("Location: ../view/PainelCliente.php?id=" . $id_veiculo);
        exit;
    }
}
