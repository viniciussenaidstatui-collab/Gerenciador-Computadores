<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>Histórico de empréstimos</title>
</head>

<body class="min-h-screen bg-[#D8DEFF] text-[#2D3DAB]">
    <main class="mx-auto max-w-7xl space-y-6 px-5 py-10">
        <header class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-[#3505BF] to-[#0074FF] px-8 py-7 text-white">
            <div>
                <h1 class="text-2xl font-bold">Histórico de empréstimos</h1>
                <p class="mt-2 text-sm text-[#D8DEFF]">Retiradas, devoluções e responsáveis que autorizaram cada operação.</p>
            </div>
            <a href="{{ route('emprestimos.create') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-bold text-[#3505BF]">Voltar aos computadores</a>
        </header>

        <section class="overflow-x-auto rounded-2xl bg-white p-5 shadow-xl">
            <table class="w-full min-w-[1000px] border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-[#D8DEFF] text-[#3505BF]">
                        <th class="p-3">Computador</th>
                        <th class="p-3">Usuário</th>
                        <th class="p-3">Retirada</th>
                        <th class="p-3">Autorizada por</th>
                        <th class="p-3">Devolução</th>
                        <th class="p-3">Autorizada por</th>
                        <th class="p-3">Situação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($emprestimos as $emprestimo)
                        <tr class="border-b border-[#EEF0FF] last:border-0">
                            <td class="p-3 font-semibold">{{ $emprestimo->computador->numero_patrimonio }}</td>
                            <td class="p-3">{{ $emprestimo->usuario->nome }}</td>
                            <td class="p-3">{{ $emprestimo->retirado_em->format('d/m/Y H:i') }}</td>
                            <td class="p-3">{{ $emprestimo->retiradaAutorizadaPor?->nome ?? '—' }}</td>
                            <td class="p-3">{{ $emprestimo->devolvido_em?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="p-3">{{ $emprestimo->devolucaoAutorizadaPor?->nome ?? '—' }}</td>
                            <td class="p-3">{{ $emprestimo->devolvido_em ? 'Devolvido' : 'Em uso' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-6 text-center text-[#497280]">Ainda não há empréstimos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-5">{{ $emprestimos->links() }}</div>
        </section>

        <a href="{{ route('home') }}" class="inline-block text-sm font-semibold text-[#3505BF]">Voltar ao início</a>
    </main>
</body>

</html>
