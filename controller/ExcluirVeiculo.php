<?php
require_once(__DIR__."/../model/veiculo.php");
require_once(__DIR__."/../model/cliente.php");
session_start();

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Buscar veículo
        $veiculo = veiculo::buscarPorID((int)$id);

        if ($veiculo) {
            // Excluir veículo
            veiculo::excluir((int)$id);

            // Excluir cliente relacionado
            cliente::excluir((int)$veiculo->cliente['id_cliente']);
        }

        $_SESSION['mensagem'] = "Veículo e cliente excluídos com sucesso!";
        $_SESSION['tipo_alerta'] = "Veículo e cliente excluídos com sucesso!";

    } catch (Exception $e) {
        $_SESSION['mensagem'] = $e->getMessage();
        $_SESSION['tipo_alerta'] = "danger";
    }
}

// Volta para a página anterior
$voltarPara = $_SERVER['HTTP_REFERER'] ?? __DIR__ . '/../view/ViewPainel.php';
header("Location: $voltarPara");
exit;