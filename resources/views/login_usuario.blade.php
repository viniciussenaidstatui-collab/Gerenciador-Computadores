<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/login.js'])
    <title>Login</title>
</head>

<body>
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center gap-4 px-6 py-5">
            <div class="text-2xl font-bold text-blue-700">SESI</div>
            <div class="text-sm font-medium text-slate-600">Sistema de Gerenciamento de Uso de Computadores</div>
        </div>
    </header>

    <main class="mx-auto max-w-lg px-6 py-12">
        <h2 class="mb-8 text-center text-2xl font-semibold text-slate-800">Login</h2>

        <form id="formulario-login" method="POST" action="{{ url('/login') }}" class="space-y-6">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">E-mail</label>
                <input type="email" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-200" id="email" name="email" value="{{ old('email') }}"
                        placeholder="Digite seu e-mail" autocomplete="username" required autofocus>
            </div>
            <div>
                <label for="senha" class="mb-2 block text-sm font-medium text-slate-700">Senha</label>
                <input type="password" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-200" id="senha" name="senha"
                        placeholder="Digite sua senha" autocomplete="current-password" required>
            </div>
            <div class="text-center">
                <button type="submit" class="w-full rounded-md bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800">ENTRAR</button>
                <p class="mt-4 text-sm text-slate-600">
                        Ainda não tem cadastro?
                        <a href="{{ url('/cadastro_usuario') }}" class="font-semibold text-blue-700 hover:underline">Cadastre-se</a>
                </p>
            </div>
        </form>
    </main>
</body>

</html>