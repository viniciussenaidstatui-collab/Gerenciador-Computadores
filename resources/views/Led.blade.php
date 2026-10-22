

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Luzes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/luzes.js'])
</head>

<body>
    <main class="container py-5">
        <h1 class="text-center my-4">Luzes</h1>
        <div class="row justify-content-center g-3">
            <div class="col-12 col-sm-6 col-lg-4">
                <button type="button" class="btn btn-success w-100 py-3" id="ligar_azul">Ligar Luz Azul</button>
                <button type="button" class="btn btn-secondary w-100 py-3 mt-2" id="desligar_azul">Desligar Luz Azul</button>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <button type="button" class="btn btn-warning w-100 py-3" id="ligar_amarela">Ligar Luz Amarela</button>
                <button type="button" class="btn btn-secondary w-100 py-3 mt-2" id="desligar_amarela">Desligar Luz Amarela</button>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <button type="button" class="btn btn-danger w-100 py-3" id="ligar_vermelha">Ligar Luz Vermelha</button>
                <button type="button" class="btn btn-secondary w-100 py-3 mt-2" id="desligar_vermelha">Desligar Luz Vermelha</button>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <button type="button" class="btn btn-danger w-100 py-3" id="ligar_todas">Ligar Todas as Luzes</button>
                <button type="button" class="btn btn-secondary w-100 py-3 mt-2" id="desligar_todas">Desligar Todas as Luzes</button>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
    <button type="button" class="btn btn-info w-100 py-3" id="status_todas">Ver Status das Luzes</button>
</div>
        </div>
    </main>
</body>
</html>