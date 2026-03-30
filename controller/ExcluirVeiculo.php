<?php
require_once(__DIR__."/../model/veiculo.php");
require_once(__DIR__."/../model/cliente.php");
require_once(__DIR__."/../config/conexao.php");
session_start();

$idVeiculo = (int)($_GET['id'] ?? 0);

if ($idVeiculo > 0)
{
    try
    {
        $pdo = (new Conexao())->conexao();
        $pdo->beginTransaction();

        // buscar veículo
        $veiculo = veiculo::buscarPorID($idVeiculo);
        if (!$veiculo)
        {
            throw new Exception("Veículo não encontrado.");
        }

        $idCliente = $veiculo->cliente['id_cliente'];

        // excluir veículo
        veiculo::excluir($idVeiculo);

        // verificar quantos veículos restaram para o cliente
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM veiculo WHERE id_cliente = :id");
        $stmt->execute([':id' => $idCliente]);
        $total = (int)$stmt->fetchColumn();

        // se não restou nenhum, excluir cliente
        if ($total === 0)
        {
            cliente::excluir($idCliente);
        }

        $pdo->commit();

        $_SESSION['mensagem'] = "Registro excluído com sucesso.";
        $_SESSION['tipo_alerta'] = "success";

    }
    catch (Throwable $e)
    {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['mensagem'] = $e->getMessage();
        $_SESSION['tipo_alerta'] = "danger";
    }
}

header("Location: ../view/ViewPainel.php");
exit;