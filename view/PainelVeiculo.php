<?php
session_start();

require_once("../model/cliente.php");

$clientes = cliente::listar();

// mensagens vindas do controller
$sucesso = $_SESSION['success'] ?? null;
$erro    = $_SESSION['error'] ?? null;

// limpa mensagens (flash messages)
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Veículo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f5f6f8;
        }

        .navbar {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        footer {
            background: #212529;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .form-label {
            font-weight: 500;
            color: #555;
        }

        .card-header-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Painel Cadastro de Veículos</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="form-card">

        <!-- CABEÇALHO -->
        <div class="card-header-custom">
            <h4 class="m-0">Novo Cadastro de Veículo</h4>
            <a href="ViewPainel.php" class="btn btn-lg btn-primary px-5">
                Voltar para Lista
            </a>
        </div>

        <!-- MENSAGENS -->
        <?php if ($sucesso): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($sucesso) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($erro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form method="POST" action="../controller/clienteCTR.php">

            <input type="hidden" name="acao" value="cadastrarVeiculo">

            <h5 class="section-title">
                <i class="bi bi-car-front-fill me-2"></i>Informações do Veículo
            </h5>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipo de Veículo</label>
                    <select name="tipoVeiculo" id="tipoVeiculo" class="form-select" required>
                        <option value="">Selecione...</option>
                        <option value="carro">Carro</option>
                        <option value="moto">Moto</option>
                        <option value="carro grande">Carro Grande</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Cor</label>
                    <input name="cor" class="form-control" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Placa</label>
                    <input type="text" name="placa" class="form-control" maxlength="7" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Vaga</label>
                    <input type="number" name="vaga" class="form-control" min="1" max="90" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Marca</label>
                    <input name="marca" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Modelo</label>
                    <input name="modelo" class="form-control" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Cliente</label>
                <select name="tipoCliente" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente->id ?>">
                            <?= htmlspecialchars($cliente->nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="text-end border-top pt-4">
                <button type="submit" name="acao" class="btn btn-lg btn-primary px-5">
                    Cadastrar Veículo
                </button>
            </div>

        </form>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="container text-center">
        <p>Painel administrativo do Estacionamento 'Salve Jorge'</p>
        <small>© 2026 Tracemys Solutions</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>