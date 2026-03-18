<?php
$sucesso = $_GET['sucesso'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente</title>

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

        /* Estilos para a pré-visualização de fotos */
        .preview-item {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
            text-align: center;
            transition: all 0.2s;
        }

        .preview-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .preview-item:hover {
            border-color: #0d6efd;
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.1);
        }

        .destaque-label {
            font-size: 0.85rem;
            cursor: pointer;
            display: block;
            margin-bottom: 0;
        }

        .is-principal {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }
    </style>
</head>

<body>

    <!-- NAVBAR PADRÃO -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Painel Cadastro de Clientes</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
            </div>
        </div>
    </nav>

    <div class="container mt-5">

        <div class="form-card">

            <div class="card-header-custom">
                <h4 class="m-0">Novo Cadastro de Cliente</h4>
                <a href="painelAdmin.php" class="btn btn-sm btn-outline-secondary">Voltar para lista</a>
            </div>

            <?php if ($sucesso == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Parabéns!</strong> Cliente cadastrado com sucesso.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif ?>

            <form id="formImovel" method="POST" action="../controller/imovelCTR.php" enctype="multipart/form-data">

                <!-- INFORMAÇÕES CLIENTE -->
                <h5 class="section-title"><i class="bi bi-info-circle me-2"></i>Informações do Cliente</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nome do Cliente</label>
                        <input type="text" name="nomeCliente" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="tel" name="telefone" class="form-control" id="telefone"
                            placeholder="(00) 00000-0000"
                            pattern="\(\d{2}\)\s\d{5}-\d{4}"
                            maxlength="15"
                            required>
                    </div>

                    <script>
                        const tel = document.getElementById('telefone');

                        tel.addEventListener('input', function(e) {
                            let v = e.target.value.replace(/\D/g, ''); // remove tudo que não é número

                            // limita a 11 dígitos (DDD + número)
                            if (v.length > 11) v = v.slice(0, 11);

                            // aplica formato fixo
                            v = v.replace(/^(\d{0,2})(\d{0,5})(\d{0,4})$/, function(_, ddd, parte1, parte2) {
                                let result = '';

                                if (ddd) result += '(' + ddd;
                                if (ddd.length === 2) result += ') ';

                                if (parte1) result += parte1;
                                if (parte2) result += '-' + parte2;

                                return result;
                            });

                            e.target.value = v;
                        });
                    </script>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tipo de Cliente</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="mensal">Mensal</option>
                            <option value="avulso">Avulso</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bairro</label>
                        <input name="bairro" class="form-control" placeholder="Ex: Av. do Contorno">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Endereço</label>
                        <input name="endereco" class="form-control" placeholder="Ex: Av. do Contorno, 60 - Itaquera, São Paulo - SP, 08220-380">
                    </div>
                </div>

                <!-- INFORMAÇÕES VEÍCULO -->
                <h5 class="section-title"><i class="bi bi-info-circle me-2"></i>Informações do Veículo</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo de Veículo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="carro">Carro</option>
                            <option value="moto">Moto</option>
                            <option value="carro grande">Carro Grande</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cor</label>
                        <input name="cor" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Placa</label>
                        <input type="text" name="placa" class="form-control" id="placa"
                            placeholder="ABC1D23"
                            maxlength="7"
                            required>
                    </div>

                    <script>
                        const placa = document.getElementById('placa');

                        placa.addEventListener('input', function(e) {
                            let v = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

                            // limita a 7 caracteres
                            v = v.slice(0, 7);

                            e.target.value = v;
                        });
                    </script>
                </div>

                <div class="row">
                        <div class="col-md-6 mb-3">
                        <label class="form-label">Marca</label>
                        <input name="marca" class="form-control" placeholder="Ex: Toyota">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Modelo</label>
                        <input name="modelo" class="form-control" placeholder="Ex: Toyota Corolla">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-5 border-top pt-4">
                    <a href="admin_lista_imoveis.html" class="btn btn-lg btn-outline-secondary px-5">Cancelar</a>
                    <button type="submit" class="btn btn-lg btn-primary px-5">Cadastrar Cliente e Veículo</button>
                </div>

            </form>
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

    <script>
        // Lógica para gerir a pré-visualização e a escolha da foto principal
        const inputFotos = document.getElementById('inputFotos');
        const containerPreview = document.getElementById('containerPreview');
        const listaFotosPreview = document.getElementById('listaFotosPreview');

        inputFotos.addEventListener('change', function() {
            listaFotosPreview.innerHTML = '';
            const files = this.files;

            if (files.length > 0) {
                containerPreview.classList.remove('d-none');

                Array.from(files).forEach((file, index) => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-6 col-md-3 col-lg-2';

                        // O valor do rádio 'index_principal' será enviado para o backend
                        // para saber qual posição do array fotos[] é a principal
                        col.innerHTML = `
                        <div class="preview-item" id="item-${index}">
                            <img src="${e.target.result}" alt="Preview">
                            <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input me-2" type="radio" 
                                       name="index_principal" id="radio-${index}" 
                                       value="${index}" ${index === 0 ? 'checked' : ''}
                                       onclick="marcarPrincipal(${index})">
                                <label class="form-check-label destaque-label" for="radio-${index}">
                                    Destaque
                                </label>
                            </div>
                        </div>
                    `;
                        listaFotosPreview.appendChild(col);

                        if (index === 0) marcarPrincipal(0);
                    }

                    reader.readAsDataURL(file);
                });
            } else {
                containerPreview.classList.add('d-none');
            }
        });

        function marcarPrincipal(index) {
            // Remove a classe de destaque de todos e adiciona apenas ao selecionado
            document.querySelectorAll('.preview-item').forEach(item => {
                item.classList.remove('is-principal');
            });
            document.getElementById(`item-${index}`).classList.add('is-principal');
        }
    </script>

</body>

</html>