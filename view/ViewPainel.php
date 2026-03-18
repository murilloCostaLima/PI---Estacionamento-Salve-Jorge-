<?php
require_once(__DIR__."/../model/cliente.php");
require_once(__DIR__."/../model/veiculo.php");

session_start();

$filtros = 
[
    'tipo'   => $_GET['fTipo']   ?? '',
    'status' => $_GET['fStatus'] ?? '',
    'busca'  => $_GET['fBusca']  ?? '',
]

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Imóveis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ícones via CDN (opcional, para os botões de ação) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body { background:#f5f6f8; }
        .navbar { box-shadow:0 2px 6px rgba(0,0,0,0.08); }
        .content-card { background:white; border-radius:10px; padding:30px; box-shadow:0 4px 12px rgba(0,0,0,0.06); }
        footer { background:#212529; color:white; padding:40px 0; margin-top:60px; }
        .section-title { font-weight:600; margin-bottom:0; }
        .table thead { background-color: #f8f9fa; }
        .badge-status { font-weight: 500; padding: 0.5em 0.8em; }
        .img-preview { width: 60px; height: 45px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>

<!-- NAVBAR PADRÃO -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Painel Imobiliária</a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Imóveis</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Usuários</a></li>
                <li class="nav-item"><a class="btn btn-outline-light ms-3" href="#">Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">

    <!--Alerta-->
    <?php if(isset($_SESSION['mensagem'])): ?>
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
        <h4 class="section-title">Gerenciamento de Imóveis</h4>
        <a href="painelCadImoveios.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Imóvel
        </a>
    </div>

    <div class="content-card">
        
        <!-- FILTROS RÁPIDOS -->
    <form method="GET" action = "painelAdmin.php">
        <div class="row mb-4">
            <div class="col-md-4">
                <input type="text" name = "fBusca" class="form-control" placeholder="Pesquisar por título, bairro ou cidade...">
            </div>
            <div class="col-md-3">
                <select class="form-select" name = "fTipo">
                    <option value="">Todos os tipos</option>
                    <option value="Casa" <?= $filtros['tipo'] == "Casa" ? 'selected' : '' ?>>Casa</option>
                    <option value="Apartamento" <?= $filtros['tipo'] == "Apartamento" ? 'selected' : '' ?>> Apartamento</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name = "fStatus">
                    <option value="">Todos os status</option>
                    <option value="disponivel">Disponível</option>
                    <option value="vendido">Vendido</option>
                    <option value="alugado">Alugado</option>
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
                        <th>Foto</th>
                        <th>Título / Localização</th>
                        <th>Tipo</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Exemplo de Linha 1 -->
                     <?php foreach($imoveis as $imovel): ?>
                    <tr>
                        <td>
                            <img src="<?= $imovel->foto_principal ?>" alt="Imóvel" class="img-preview">
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= $imovel->titulo ?></div>
                            <small class="text-muted"><?= $imovel->bairro ?> - <?= $imovel->cidade ?> - <?= $imovel->estado ?></small>
                        </td>
                        <td><?= ucfirst($imovel->tipo) ?></td>
                        <td>R$ 1.250.000,00</td>
                        <td>
                            <?php
                                $cor = ['disponivel'=>'success', 'vendido'=>'danger', 'alugado'=>'warning'];
                                $statusCor = $cor[$imovel->status] ?? 'secundary'
                            ?>
                            <span class="badge bg-<?= $statusCor ?> badge-status"><?= $imovel->status ?></span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="#" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="../controller/imovelCTR.php?excluir_id=<?= $imovel->id ?>"
                                    class="btn btn-sm btn-outline-danger" title="Excluir">
                                    <i class="bi bi-trash" onclick="return confirm('Deseja excluir este imóvel?')"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINAÇÃO -->
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled"><a class="page-link" href="#">Anterior</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Próximo</a></li>
            </ul>
        </nav>

    </div>
</div>

<!-- FOOTER PADRÃO -->
<footer>
    <div class="container text-center">
        <p>Painel administrativo da imobiliária</p>
        <small>© 2026 Sistema interno</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>