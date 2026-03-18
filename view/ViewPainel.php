<?php
require_once(__DIR__ . "/../model/veiculo.php");
require_once(__DIR__ . "/../model/cliente.php");

session_start();

// Buscar veículos para a tabela
$filtros = [
    'tipo_veiculo' => $_GET['fTipo']   ?? '',
    'tipo_cliente' => $_GET['fStatus'] ?? '',
    'busca'        => $_GET['fBusca']  ?? '',
];

$veiculos = veiculo::listarComFiltros($filtros);
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Clientes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ícones via CDN (opcional, para os botões de ação) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f5f6f8;
        }

        .navbar {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .content-card {
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
            margin-bottom: 0;
        }

        .table thead {
            background-color: #f8f9fa;
        }

        .badge-status {
            font-weight: 500;
            padding: 0.5em 0.8em;
        }

        .img-preview {
            width: 60px;
            height: 45px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>

<body>

    <!-- NAVBAR PADRÃO -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Painel do Estacionamento</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto">
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">

        <!--Alerta-->
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-<?= $_SESSION['tipo_alerta'] ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['tipo_alerta'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
            unset($_SESSION['mensagem']);
            unset($_SESSION['tipo_alerta']);
            ?>
        <?php endif ?>

        <!-- CABEÇALHO DA PÁGINA -->

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="section-title mb-0">Gerenciamento de clientes</h4>

            <div class="d-flex gap-2">
                <button class="btn btn-lg btn-primary px-5">
                    Cadastrar Cliente
                </button>
                <button class="btn btn-lg btn-primary px-5">
                    Cadastrar Veículo
                </button>
            </div>
        </div>


        <div class="content-card">

            <!-- FILTROS RÁPIDOS -->
            <form method="GET" action="ViewPainel.php">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" name="fBusca" class="form-control" placeholder="Pesquisar por Placa, Nome, Telefone...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="fTipo">
                            <option value="">Todos os Veículos</option>
                            <option value="carro" <?= $filtros['tipo_veiculo'] == "carro" ? 'selected' : '' ?>>Carro</option>
                            <option value="moto" <?= $filtros['tipo_veiculo'] == "moto" ? 'selected' : '' ?>> Moto</option>
                            <option value="carro grande" <?= $filtros['tipo_veiculo'] == "carro grande" ? 'selected' : '' ?>>Carro Grande</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="fStatus">
                            <option value="">Todos os tipos de Cliente</option>
                            <option value="mensal">Mensal</option>
                            <option value="avulso">Avulso</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100">Filtrar</button>
                    </div>
                </div>

                <!-- TABELA DE DADOS -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nome / Telefone</th>
                                <th>Veículo / Cor</th>
                                <th>Vaga</th>
                                <th>Placa do Veículo</th>
                                <th>Status do Cliente</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Exemplo de Linha 1 -->
                        <tbody>
                            <?php if (!empty($veiculos)): ?>
                                <?php foreach ($veiculos as $v): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($v['cliente_nome']) ?></strong><br>
                                            <small><?= htmlspecialchars($v['cliente_telefone']) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= ucfirst($v['modelo']) ?></strong><br>
                                            <small><?= htmlspecialchars($v['cor']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($v['codigo_vaga']) ?></td>
                                        <td><?= htmlspecialchars($v['placa']) ?></td>
                                        <td>
                                            <?php $tipoCliente = $v['tipo_cliente'];

                                            $cores = ['Mensal' => 'primary', 'Avulso' => 'danger'];

                                            $corBadge = $cores[$tipoCliente] ?? 'secondary'; ?>
                                            <span class="badge bg-<?= $corBadge ?>">
                                                <?= htmlspecialchars($tipoCliente) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Deseja excluir este veículo?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Nenhum veículo encontrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINAÇÃO -->
                <!-- <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#">Anterior</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Próximo</a></li>
                    </ul>
                </nav> -->

        </div>
    </div>

    <!-- FOOTER PADRÃO -->
    <footer>
        <div class="container text-center">
            <p>Painel administrativo do estacionamento</p>
            <small>© 2026 Sistema interno</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>