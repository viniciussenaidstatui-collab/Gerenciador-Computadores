<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>Editar responsável</title>
</head>

<body class="min-h-screen bg-[#D8DEFF] text-[#2D3DAB]">
    <main class="mx-auto max-w-2xl px-5 py-10">
        <section class="rounded-2xl bg-white p-6 shadow-xl sm:p-8">
            <h1 class="mb-6 text-2xl font-bold text-[#3505BF]">Editar responsável</h1>
            <form method="POST" action="{{ route('admin.responsaveis.update', $responsavel) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <label class="block text-sm font-semibold">Nome
                    <input name="nome" value="{{ old('nome', $responsavel->nome) }}" required maxlength="120" class="mt-1 w-full rounded-lg border border-[#D8DEFF] px-3 py-2.5 font-normal">
                    @error('nome')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold">UID do cartão
                    <input name="rfid_uid" value="{{ old('rfid_uid', $responsavel->rfid_uid) }}" required maxlength="20" pattern="(?:[A-Fa-f0-9]{8}|[A-Fa-f0-9]{10}|[A-Fa-f0-9]{14}|[A-Fa-f0-9]{20})" class="mt-1 w-full rounded-lg border border-[#D8DEFF] px-3 py-2.5 font-mono font-normal">
                    @error('rfid_uid')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold">Ação autorizada
                    <select name="acao_permitida" required class="mt-1 w-full rounded-lg border border-[#D8DEFF] px-3 py-2.5 font-normal">
                        <option value="retirada" @selected(old('acao_permitida', $responsavel->acao_permitida) === 'retirada')>Somente empréstimo</option>
                        <option value="devolucao" @selected(old('acao_permitida', $responsavel->acao_permitida) === 'devolucao')>Somente devolução</option>
                        <option value="ambas" @selected(old('acao_permitida', $responsavel->acao_permitida) === 'ambas')>Empréstimo e devolução</option>
                    </select>
                    @error('acao_permitida')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <div class="flex gap-3">
                    <button class="rounded-lg bg-[#3505BF] px-5 py-2.5 font-bold text-white">Salvar</button>
                    <a href="{{ route('admin.responsaveis.index') }}" class="rounded-lg border border-[#3505BF] px-5 py-2.5 font-semibold text-[#3505BF]">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
