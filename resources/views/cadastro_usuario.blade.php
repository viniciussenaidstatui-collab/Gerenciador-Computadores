<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cadastro de Usuário</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .card-cadastro {
            max-width: 700px;
            margin: 60px auto;
            padding: 30px;
        }
    </style>
</head>
<body>

    <div class="card card-cadastro shadow-sm">
        <h3 class="mb-4 text-center">Cadastro de Usuário</h3>

        <form id="form-cadastro-usuario">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="nome" class="form-label">Nome completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" minlength="6" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="cpf" class="form-label">CPF</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" maxlength="11" placeholder="Somente números" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="data_nascimento" class="form-label">Data de nascimento</label>
                    <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
                </div>
            </div>

            <button type="submit" id="btn-cadastrar" class="btn btn-primary w-100 mt-2">Cadastrar</button>
        </form>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script de cadastro -->
    <script src="{{ asset('cadastro_usuario.js') }}"></script>
</body>
</html>