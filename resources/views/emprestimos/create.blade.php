<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite('resources/css/app.css')

    <title>Empréstimos</title>
</head>

<body class="min-h-screen bg-[#D8DEFF] text-[#2D3DAB]">

    <main class="mx-auto max-w-4xl px-5 py-10">
        <section class="overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="bg-gradient-to-r from-[#3505BF] to-[#0074FF] px-8 py-8 text-white">
                <h1 class="text-2xl font-bold">Retirada e devolução</h1>
                <p class="mt-1 text-sm text-[#D8DEFF]">Controle os computadores em uso pelo seu usuário.</p>
            </div>

            <div class="space-y-8 p-8">
                @if (session('success'))
                    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <h2 class="text-lg font-bold text-[#3505BF]">Retirar computador</h2>

                    <form method="POST" action="{{ route('emprestimos.store') }}" class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end">
                        @csrf

                        <div class="flex-1">
                            <label for="computador_id" class="mb-1.5 block text-sm font-semibold text-[#2D3DAB]">Computador disponível</label>
                            <select id="computador_id" name="computador_id" class="w-full rounded-lg border border-[#D8DEFF] bg-white px-4 py-3 text-[#497280] outline-none" @disabled($computadoresDisponiveis->isEmpty())>
                                <option value="">Selecione um computador</option>
                                @foreach ($computadoresDisponiveis as $computador)
                                    <option value="{{ $computador->id }}" @selected(old('computador_id') == $computador->id)>
                                        {{ $computador->numero_patrimonio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="rounded-lg bg-[#3505BF] px-5 py-3 font-semibold text-white hover:bg-[#2D3DAB] disabled:cursor-not-allowed disabled:opacity-50" @disabled($computadoresDisponiveis->isEmpty())>
                            Retirar
                        </button>
                    </form>

                    @if ($computadoresDisponiveis->isEmpty())
                        <p class="mt-3 text-sm text-[#497280]">Nenhum computador está disponível no momento.</p>
                    @endif
                </div>

                <div class="border-t border-[#D8DEFF] pt-8">
                    <h2 class="text-lg font-bold text-[#3505BF]">Meus empréstimos ativos</h2>

                    @if ($emprestimosAtivos->isEmpty())
                        <p class="mt-4 text-sm text-[#497280]">Você não possui computadores retirados.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($emprestimosAtivos as $emprestimo)
                                <div class="flex flex-col gap-4 rounded-lg border border-[#D8DEFF] p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-semibold text-[#2D3DAB]">{{ $emprestimo->computador->numero_patrimonio }}</p>
                                        <p class="text-sm text-[#497280]">Retirado em {{ $emprestimo->retirado_em->format('d/m/Y H:i') }}</p>
                                    </div>

                                    <form method="POST" action="{{ route('emprestimos.devolver', $emprestimo) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg border border-[#3505BF] px-5 py-2.5 font-semibold text-[#3505BF] hover:bg-[#F5F6FF]">
                                            Devolver
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <a href="{{ route('home') }}" class="inline-block text-sm font-semibold text-[#3505BF]">Voltar para início</a>
            </div>
        </section>
    </main>

</body>

</html>
