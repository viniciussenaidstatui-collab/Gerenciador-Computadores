<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cadastro com RFID</title>
    @vite('resources/css/app.css')
</head>

<body class="min-h-screen bg-[#D8DEFF] text-[#2D3DAB]">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center justify-center px-5 py-10">
        <section class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl">
            <header class="bg-gradient-to-r from-[#3505BF] to-[#0074FF] px-7 py-8 text-white sm:px-10">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-white/80 hover:text-white">← Voltar ao início</a>
                <h1 class="mt-5 text-2xl font-bold sm:text-3xl">Cadastrar computador com RFID</h1>
                <p class="mt-2 text-sm text-[#D8DEFF]">O UID lido pelo ESP32 será vinculado ao computador no sistema.</p>
            </header>

            <div class="space-y-6 p-7 sm:p-10">
                <p class="text-sm text-[#497280]">Olá, <span class="font-semibold text-[#3505BF]">{{ $usuario->nome }}</span>. Complete os dados para iniciar.</p>

                <div class="rounded-xl border border-[#D8DEFF] bg-[#F5F6FF] p-4">
                    <p class="font-semibold text-[#3505BF]">Como funciona</p>
                    <ol class="mt-2 list-inside list-decimal space-y-1 text-sm text-[#497280]">
                        <li>Informe o número de patrimônio e confirme sua senha.</li>
                        <li>Abra o Monitor Serial do ESP32 em 115200 baud e leia o UID da etiqueta.</li>
                        <li>Informe o UID abaixo e salve o cadastro.</li>
                    </ol>
                </div>

                <div id="rfid-feedback" class="hidden rounded-lg px-4 py-3 text-sm" role="status" aria-live="polite"></div>

                <form id="rfid-form" action="{{ route('computadores.rfid.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="numero_patrimonio" class="mb-1.5 block text-sm font-semibold">Número de patrimônio</label>
                        <input id="numero_patrimonio" name="numero_patrimonio" type="text" maxlength="16" required autofocus value="{{ old('numero_patrimonio') }}" placeholder="Ex.: NOTEBOOK-01" class="w-full rounded-lg border border-[#D8DEFF] px-4 py-3 outline-none transition focus:border-[#0074FF] focus:ring-2 focus:ring-[#D8DEFF]">
                        <p class="mt-1.5 text-xs text-[#497280]">Até 16 caracteres: letras sem acento, números, hífen ou sublinhado.</p>
                        <p class="hidden pt-1 text-sm text-red-600" data-error="numero_patrimonio"></p>
                    </div>

                    <div>
                        <label for="rfid_uid" class="mb-1.5 block text-sm font-semibold">UID da etiqueta RFID</label>
                        <input id="rfid_uid" name="rfid_uid" type="text" maxlength="20" required
                            pattern="[A-Fa-f0-9]{8}|[A-Fa-f0-9]{10}|[A-Fa-f0-9]{14}|[A-Fa-f0-9]{20}"
                            placeholder="Ex.: 43E67D14"
                            class="w-full rounded-lg border border-[#D8DEFF] px-4 py-3 uppercase outline-none transition focus:border-[#0074FF] focus:ring-2 focus:ring-[#D8DEFF]">
                        <p class="mt-1.5 text-xs text-[#497280]">Copie somente o UID exibido após “UID detectado:” no Monitor Serial.</p>
                        <p class="hidden pt-1 text-sm text-red-600" data-error="rfid_uid"></p>
                    </div>

                    <div>
                        <label for="senha_confirmacao" class="mb-1.5 block text-sm font-semibold">Confirme sua senha</label>
                        <input id="senha_confirmacao" name="senha_confirmacao" type="password" required autocomplete="current-password" placeholder="Sua senha de acesso" class="w-full rounded-lg border border-[#D8DEFF] px-4 py-3 outline-none transition focus:border-[#0074FF] focus:ring-2 focus:ring-[#D8DEFF]">
                        <p class="hidden pt-1 text-sm text-red-600" data-error="senha_confirmacao"></p>
                    </div>

                    <button id="rfid-submit" type="submit" class="w-full rounded-lg bg-[#0074FF] px-5 py-3.5 font-bold text-white transition hover:bg-[#3505BF] focus:outline-none focus:ring-4 focus:ring-[#D8DEFF] disabled:cursor-not-allowed disabled:opacity-50">
                        Cadastrar computador
                    </button>
                </form>

                <p class="text-center text-xs text-[#497280]">O ESP32 não grava a etiqueta; ele apenas lê o UID para identificação.</p>
            </div>
        </section>
    </main>
</body>

</html>
