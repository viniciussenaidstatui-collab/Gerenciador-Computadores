<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Gerenciador de Computadores</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        :root {
            --azul-claro: #D8DEFF;
            --azul-vivo: #0074FF;
            --roxo: #3505BF;
            --azul-medio: #2D3DAB;
            --azul-cinza: #497280;
        }

        body {
            min-height: 100vh;
            background-color: var(--azul-claro);
            color: var(--azul-medio);
            display: flex;
            flex-direction: column;
        }

        /* Barra superior */

        header {
            height: 75px;
            padding: 0 50px;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #c7cff5;
        }

        .logo {
            font-size: 21px;
            font-weight: bold;
            color: var(--roxo);
        }

        #logout-button {
            border: none;
            background: transparent;
            color: var(--azul-medio);
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 8px;
            transition: 0.2s;
        }

        #logout-button:hover {
            background-color: var(--azul-claro);
            color: var(--roxo);
        }

        /* Conteúdo principal */

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .welcome {
            width: 100%;
            max-width: 850px;
            background-color: white;
            border-radius: 20px;
            padding: 65px;
            display: flex;
            align-items: center;
            gap: 55px;
            box-shadow: 0 15px 40px rgba(53, 5, 191, 0.12);
        }

        .icon {
            min-width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(
                145deg,
                var(--azul-vivo),
                var(--roxo)
            );

            display: flex;
            align-items: center;
            justify-content: center;

            color: white;
            font-size: 60px;
            font-weight: bold;
        }

        .content h1 {
            color: var(--roxo);
            font-size: 38px;
            margin-bottom: 18px;
        }

        .content p {
            color: var(--azul-cinza);
            font-size: 17px;
            line-height: 1.6;
            margin-bottom: 30px;
            max-width: 500px;
        }

        .btn {
            display: inline-block;
            padding: 13px 25px;
            border-radius: 8px;
            background-color: var(--azul-vivo);
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s;
        }

        .btn:hover {
            background-color: var(--roxo);
            transform: translateY(-2px);
        }

        /* Rodapé */

        footer {
            text-align: center;
            padding: 18px;
            color: var(--azul-cinza);
            font-size: 13px;
        }

        /* Responsividade */

        @media (max-width: 700px) {
            header {
                padding: 0 20px;
            }

            .welcome {
                flex-direction: column;
                text-align: center;
                padding: 45px 25px;
                gap: 30px;
            }

            .content p {
                margin-left: auto;
                margin-right: auto;
            }

            .content h1 {
                font-size: 30px;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="logo">
            Gerenciador de Computadores
        </div>

        <button id="logout-button" type="button">
            Sair
        </button>
    </header>

    <main>

        <section class="welcome">

            <div class="icon">
                PC
            </div>

            <div class="content">

                <h1>
                    Bem vindo ao Gerenciador de Computadores.
                </h1>

                <p>
                    Gerencie e organize as informações dos computadores
                    de forma simples e prática.
                </p>

                <a href="/computadores/registrar" class="btn">
                    Cadastrar Computador
                </a>

                <a href="{{ route('emprestimos.create') }}" class="btn">
                    Retirar ou devolver computador
                </a>

            </div>

        </section>

    </main>

    <footer>
        Gerenciador de Computadores
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.cookie@1.4.1/jquery.cookie.min.js"></script>
    @vite('resources/js/authenticated.js')

</body>

</html>