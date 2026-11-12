<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/emprestimos-rfid.js'])
    <title>Empréstimos de computadores</title>
</head>

<body class="min-h-screen bg-[#D8DEFF] text-[#2D3DAB]">
    <main class="mx-auto max-w-5xl space-y-6 px-5 py-10">
        <header class="rounded-2xl bg-gradient-to-r from-[#3505BF] to-[#0074FF] px-8 py-7 text-white">
            <h1 class="text-2xl font-bold">Empréstimos do laboratório</h1>
            <p class="mt-2 text-sm text-[#D8DEFF]">Escolha um computador e aproxime o cartão do responsável no leitor.</p>
        </header>

        <div id="rfid-feedback" class="hidden rounded-lg px-4 py-3 text-sm" role="status" aria-live="polite"></div>

        <section class="rounded-2xl bg-white p-6 shadow-xl sm:p-8">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-[#3505BF]">Computadores</h2>
                <a href="{{ route('emprestimos.historico') }}" class="text-sm font-semibold text-[#3505BF] hover:underline">Ver histórico</a>
            </div>

            @if ($computadores->isEmpty())
                <p class="rounded-lg bg-[#F5F6FF] p-4 text-sm text-[#497280]">Nenhum computador cadastrado.</p>
            @else
                <div class="space-y-3">
                    @foreach ($computadores as $computador)
                        @php($emprestimoAtivo = $computador->emprestimos->first())
                        <article class="flex flex-col gap-4 rounded-xl border border-[#D8DEFF] p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-bold text-[#3505BF]">{{ $computador->numero_patrimonio }}</h3>
                                @if ($emprestimoAtivo)
                                    <p class="mt-1 text-sm text-[#497280]">Emprestado a {{ $emprestimoAtivo->usuario->nome }}</p>
                                    <p class="text-xs text-[#497280]">Desde {{ $emprestimoAtivo->retirado_em->format('d/m/Y H:i') }}</p>
                                @else
                                    <p class="mt-1 text-sm text-[#497280]">Disponível</p>
                                @endif
                            </div>
                            <button type="button"
                                data-select-url="{{ route('computadores.selecionar', $computador) }}"
                                class="rfid-select-button rounded-lg bg-[#3505BF] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#0074FF] disabled:cursor-wait disabled:opacity-60">
                                {{ $emprestimoAtivo ? 'Devolver' : 'Emprestar' }}
                            </button>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <a href="{{ route('home') }}" class="inline-block text-sm font-semibold text-[#3505BF]">Voltar ao início</a>
    </main>
</body>

</html>
