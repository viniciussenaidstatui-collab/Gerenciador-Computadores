<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cadastro com RFID</title>
    @vite(['resources/css/app.css', 'resources/js/rfid.js'])
</head>

<body class="min-h-screen bg-[#D8DEFF] text-[#2D3DAB]">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center justify-center px-5 py-10">
        <section class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl">
            <header class="bg-gradient-to-r from-[#3505BF] to-[#0074FF] px-7 py-8 text-white sm:px-10">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-white/80 hover:text-white">← Voltar ao início</a>
                <h1 class="mt-5 text-2xl font-bold sm:text-3xl">Cadastrar computador com RFID</h1>
                <p class="mt-2 text-sm text-[#D8DEFF]">O patrimônio será gravado na etiqueta e vinculado ao computador no sistema.</p>
            </header>

            <div class="space-y-6 p-7 sm:p-10">
                <p class="text-sm text-[#497280]">Olá, <span class="font-semibold text-[#3505BF]">{{ $usuario->nome }}</span>. Complete os dados para iniciar.</p>

                <div class="rounded-xl border border-[#D8DEFF] bg-[#F5F6FF] p-4">
                    <p class="font-semibold text-[#3505BF]">Como funciona</p>
                    <ol class="mt-2 list-inside list-decimal space-y-1 text-sm text-[#497280]">
                        <li>Informe o número de patrimônio e confirme sua senha.</li>
                        <li>Clique em iniciar e aproxime a etiqueta do leitor RC522.</li>
                        <li>Aguarde a confirmação da gravação e do cadastro.</li>
                    </ol>
                </div>

                @unless ($esp32Url)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
                        O endereço do ESP32 ainda não foi configurado. Defina <code>ESP32_BASE_URL</code> no arquivo <code>.env</code> usando o IP exibido no Monitor Serial.
                    </div>
                @endunless

                <div id="rfid-feedback" class="hidden rounded-lg px-4 py-3 text-sm" role="status" aria-live="polite"></div>

                <form id="rfid-form" action="{{ route('computadores.rfid.store') }}" method="POST" data-esp32-url="{{ $esp32Url }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="numero_patrimonio" class="mb-1.5 block text-sm font-semibold">Número de patrimônio</label>
                        <input id="numero_patrimonio" name="numero_patrimonio" type="text" maxlength="16" required autofocus value="{{ old('numero_patrimonio') }}" placeholder="Ex.: NOTEBOOK-01" class="w-full rounded-lg border border-[#D8DEFF] px-4 py-3 outline-none transition focus:border-[#0074FF] focus:ring-2 focus:ring-[#D8DEFF]">
                        <p class="mt-1.5 text-xs text-[#497280]">Até 16 caracteres: letras sem acento, números, hífen ou sublinhado.</p>
                        <p class="hidden pt-1 text-sm text-red-600" data-error="numero_patrimonio"></p>
                    </div>

                    <div>
                        <label for="senha_confirmacao" class="mb-1.5 block text-sm font-semibold">Confirme sua senha</label>
                        <input id="senha_confirmacao" name="senha_confirmacao" type="password" required autocomplete="current-password" placeholder="Sua senha de acesso" class="w-full rounded-lg border border-[#D8DEFF] px-4 py-3 outline-none transition focus:border-[#0074FF] focus:ring-2 focus:ring-[#D8DEFF]">
                        <p class="hidden pt-1 text-sm text-red-600" data-error="senha_confirmacao"></p>
                    </div>

                    <button id="rfid-submit" type="submit" @disabled(! $esp32Url) class="w-full rounded-lg bg-[#0074FF] px-5 py-3.5 font-bold text-white transition hover:bg-[#3505BF] focus:outline-none focus:ring-4 focus:ring-[#D8DEFF] disabled:cursor-not-allowed disabled:opacity-50">
                        Iniciar gravação e aproximar etiqueta
                    </button>
                </form>

                <p class="text-center text-xs text-[#497280]">O leitor aguarda a etiqueta por até 20 segundos.</p>
            </div>
        </section>
    </main>
</body>

</html>
