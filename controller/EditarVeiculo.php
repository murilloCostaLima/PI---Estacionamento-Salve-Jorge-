<?php

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../view/ViewPainel.php");
    exit;
}

header("Location: ../view/PainelCliente.php?id=" . (int)$id);
exit;

if ($acao === 'editarCompleto') {

    $id_cliente = (int)$_POST['id_cliente'];
    $id_veiculo = (int)$_POST['id_veiculo'];

    try {
        $pdo = (new Conexao())->conexao();
        $pdo->beginTransaction();

        $veiculoAtual = veiculo::buscarPorID($id_veiculo);
        if (!$veiculoAtual) {
            throw new Exception("Veículo não encontrado.");
        }

        // CLIENTE
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

        // VAGA
        $novaVaga  = (int)$_POST['vaga'];
        if ($novaVaga <= 0) {
            throw new Exception("Vaga inválida.");
        }

        $vagaAtual = $veiculoAtual->vaga['codigo_vaga'] ?? null;

        if ($novaVaga !== $vagaAtual) {

            if ($vagaAtual !== null) {
                $stmt = $pdo->prepare("
                    UPDATE vaga SET disponibilidade = 'disponivel'
                    WHERE codigo_vaga = :vaga
                ");
                $stmt->execute([':vaga' => $vagaAtual]);
            }

            $stmt = $pdo->prepare("
                SELECT id_vaga FROM vaga
                WHERE codigo_vaga = :vaga
                  AND disponibilidade = 'disponivel'
                FOR UPDATE
            ");
            $stmt->execute([':vaga' => $novaVaga]);

            $idVagaNova = $stmt->fetchColumn();

            if (!$idVagaNova) {
                throw new Exception("A nova vaga não está disponível.");
            }

            $stmt = $pdo->prepare("
                UPDATE vaga SET disponibilidade = 'ocupada'
                WHERE id_vaga = :id
            ");
            $stmt->execute([':id' => $idVagaNova]);
        } else {
            $idVagaNova = $veiculoAtual->id_vaga;
        }

        // VEÍCULO
        veiculo::atualizar(
            $id_veiculo,
            $idVagaNova,
            $id_cliente,
            $veiculoAtual->placa,
            $_POST['cor'],
            $_POST['marca'],
            $_POST['modelo'],
            $veiculoAtual->tipo_veiculo,
            $veiculoAtual->hr_entrada,
            $veiculoAtual->hr_saida
        );

        $pdo->commit();

        $_SESSION['success'] = "Registro atualizado com sucesso!";
        $_SESSION['flash_from'] = 'cadastrarCompleto';
        header("Location: ../view/ViewPainel.php");
        exit;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['error'] = $e->getMessage();
        $_SESSION['flash_from'] = 'cadastrarCompleto';
        header("Location: ../view/PainelCliente.php?id=" . $id_veiculo);
        exit;
    }
}
