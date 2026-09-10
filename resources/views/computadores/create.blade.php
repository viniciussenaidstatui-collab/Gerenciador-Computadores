<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite('resources/css/app.css')

    <title>Registrar computador</title>
</head>

<body class="min-h-screen bg-[#D8DEFF] text-[#2D3DAB]">

    <main class="flex min-h-screen items-center justify-center px-5 py-10">

        <section class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl">

            <!-- Cabeçalho -->

            <div class="bg-gradient-to-r from-[#3505BF] to-[#0074FF] px-8 py-8 text-white">

                <div class="flex items-center gap-4">

                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/20 text-2xl font-bold">
                        PC
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold">
                            Registrar computador
                        </h1>

                        <p class="mt-1 text-sm text-[#D8DEFF]">
                            Cadastre um computador no sistema
                        </p>
                    </div>

                </div>

            </div>


            <!-- Conteúdo -->

            <div class="p-8">

                @if (session('success'))
                    <div
                        class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                        role="alert"
                    >
                        {{ session('success') }}
                    </div>
                @endif


                <!-- Dados do usuário -->

                <div class="mb-7">

                    <div class="mb-4">
                        <h2 class="text-lg font-bold text-[#3505BF]">
                            Dados do usuário
                        </h2>

                        <p class="text-sm text-[#497280]">
                            Informações vinculadas ao cadastro atual.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label
                                for="nome"
                                class="mb-1.5 block text-sm font-semibold text-[#2D3DAB]"
                            >
                                Nome
                            </label>

                            <input
                                id="nome"
                                type="text"
                                value="{{ $usuario->nome }}"
                                readonly
                                class="w-full rounded-lg border border-[#D8DEFF] bg-[#F5F6FF] px-4 py-3 text-[#497280] outline-none"
                            >
                        </div>


                        <div>
                            <label
                                for="cpf"
                                class="mb-1.5 block text-sm font-semibold text-[#2D3DAB]"
                            >
                                CPF
                            </label>

                            <input
                                id="cpf"
                                type="text"
                                value="{{ $usuario->cpf }}"
                                readonly
                                class="w-full rounded-lg border border-[#D8DEFF] bg-[#F5F6FF] px-4 py-3 text-[#497280] outline-none"
                            >
                        </div>


                        <div>
                            <label
                                for="email"
                                class="mb-1.5 block text-sm font-semibold text-[#2D3DAB]"
                            >
                                E-mail
                            </label>

                            <input
                                id="email"
                                type="email"
                                value="{{ $usuario->email }}"
                                readonly
                                class="w-full rounded-lg border border-[#D8DEFF] bg-[#F5F6FF] px-4 py-3 text-[#497280] outline-none"
                            >
                        </div>

                    </div>

                </div>


                <!-- Separador -->

                <div class="mb-7 border-t border-[#D8DEFF]"></div>


                <!-- Formulário -->

                <form
                    method="POST"
                    action="{{ route('computadores.store') }}"
                    class="space-y-6"
                >

                    @csrf


                    <div>

                        <h2 class="text-lg font-bold text-[#3505BF]">
                            Dados do computador
                        </h2>

                        <p class="mb-5 text-sm text-[#497280]">
                            Informe os dados necessários para realizar o registro.
                        </p>


                        <!-- Número -->

                        <div>
                            <label
                                for="numero"
                                class="mb-1.5 block text-sm font-semibold text-[#2D3DAB]"
                            >
                                Número do computador
                            </label>

                            <input
                                id="numero"
                                name="numero"
                                type="number"
                                value="{{ old('numero') }}"
                                required
                                autofocus
                                placeholder="Ex: 001"
                                class="w-full rounded-lg border border-[#D8DEFF] px-4 py-3 text-[#2D3DAB] outline-none transition focus:border-[#0074FF] focus:ring-2 focus:ring-[#D8DEFF]"
                            >

                            @error('numero')
                                <p class="mt-1.5 text-sm font-medium text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>


                    <!-- Senha -->

                    <div>

                        <label
                            for="senha_confirmacao"
                            class="mb-1.5 block text-sm font-semibold text-[#2D3DAB]"
                        >
                            Confirme sua senha
                        </label>

                        <input
                            id="senha_confirmacao"
                            name="senha_confirmacao"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="Digite sua senha"
                            class="w-full rounded-lg border border-[#D8DEFF] px-4 py-3 text-[#2D3DAB] outline-none transition focus:border-[#0074FF] focus:ring-2 focus:ring-[#D8DEFF]"
                        >

                        @error('senha_confirmacao')
                            <p class="mt-1.5 text-sm font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    <!-- Botão -->

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-[#0074FF] px-5 py-3.5 font-bold text-white transition hover:bg-[#3505BF] focus:outline-none focus:ring-4 focus:ring-[#D8DEFF]"
                    >
                        Registrar computador
                    </button>

                </form>

            </div>

        </section>

    </main>

</body>

</html>

