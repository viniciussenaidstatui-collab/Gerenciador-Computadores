<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
    <title>Cadastro de responsáveis</title>
</head>

<body class="min-h-screen bg-[#D8DEFF] text-[#2D3DAB]">
    <main class="mx-auto max-w-5xl space-y-6 px-5 py-10">
        <header class="rounded-2xl bg-gradient-to-r from-[#3505BF] to-[#0074FF] px-8 py-7 text-white">
            <h1 class="text-2xl font-bold">Responsáveis RFID</h1>
            <p class="mt-2 text-sm text-[#D8DEFF]">Cadastre o nome e o UID copiado do Monitor Serial do ESP32.</p>
        </header>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
        @endif

        <section class="rounded-2xl bg-white p-6 shadow-xl sm:p-8">
            <h2 class="mb-4 text-lg font-bold text-[#3505BF]">Novo responsável</h2>
            <form method="POST" action="{{ route('admin.responsaveis.store') }}" class="grid gap-4 sm:grid-cols-[1fr_1fr_1fr_auto]">
                @csrf
                <label class="text-sm font-semibold">Nome
                    <input name="nome" value="{{ old('nome') }}" required maxlength="120" class="mt-1 w-full rounded-lg border border-[#D8DEFF] px-3 py-2.5 font-normal">
                    @error('nome')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-semibold">UID do cartão
                    <input name="rfid_uid" value="{{ old('rfid_uid') }}" required maxlength="20" pattern="(?:[A-Fa-f0-9]{8}|[A-Fa-f0-9]{10}|[A-Fa-f0-9]{14}|[A-Fa-f0-9]{20})" class="mt-1 w-full rounded-lg border border-[#D8DEFF] px-3 py-2.5 font-mono font-normal">
                    @error('rfid_uid')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-semibold">Ação autorizada
                    <select name="acao_permitida" required class="mt-1 w-full rounded-lg border border-[#D8DEFF] px-3 py-2.5 font-normal">
                        <option value="retirada" @selected(old('acao_permitida') === 'retirada')>Somente empréstimo</option>
                        <option value="devolucao" @selected(old('acao_permitida') === 'devolucao')>Somente devolução</option>
                        <option value="ambas" @selected(old('acao_permitida', 'ambas') === 'ambas')>Empréstimo e devolução</option>
                    </select>
                    @error('acao_permitida')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <button class="self-end rounded-lg bg-[#3505BF] px-5 py-2.5 font-bold text-white hover:bg-[#0074FF]">Cadastrar</button>
            </form>
        </section>

        <section class="space-y-3 rounded-2xl bg-white p-6 shadow-xl sm:p-8">
            <h2 class="mb-4 text-lg font-bold text-[#3505BF]">Responsáveis cadastrados</h2>
            @forelse ($responsaveis as $responsavel)
                <article class="flex flex-col gap-3 rounded-xl border border-[#D8DEFF] p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-bold">{{ $responsavel->nome }}</p>
                        <p class="font-mono text-sm text-[#497280]">{{ $responsavel->rfid_uid }}</p>
                        <p class="text-xs text-[#497280]">{{ ['retirada' => 'Somente empréstimo', 'devolucao' => 'Somente devolução', 'ambas' => 'Empréstimo e devolução'][$responsavel->acao_permitida] ?? $responsavel->acao_permitida }}</p>
                        <p class="text-xs {{ $responsavel->ativo ? 'text-green-700' : 'text-red-600' }}">{{ $responsavel->ativo ? 'Ativo' : 'Inativo' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.responsaveis.edit', $responsavel) }}" class="rounded-lg border border-[#3505BF] px-4 py-2 text-sm font-semibold text-[#3505BF]">Editar</a>
                        <form method="POST" action="{{ route('admin.responsaveis.ativo', $responsavel) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded-lg px-4 py-2 text-sm font-semibold {{ $responsavel->ativo ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">{{ $responsavel->ativo ? 'Desativar' : 'Ativar' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="text-sm text-[#497280]">Nenhum responsável cadastrado.</p>
            @endforelse
        </section>

        <a href="{{ route('home') }}" class="inline-block text-sm font-semibold text-[#3505BF]">Voltar ao início</a>
    </main>
</body>

</html>
