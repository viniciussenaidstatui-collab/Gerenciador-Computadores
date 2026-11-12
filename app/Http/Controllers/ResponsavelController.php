<?php

namespace App\Http\Controllers;

use App\Models\Responsavel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResponsavelController extends Controller
{
    public function index(): View
    {
        return view('responsaveis.index', [
            'responsaveis' => Responsavel::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['rfid_uid' => strtoupper(trim((string) $request->input('rfid_uid', '')))]);
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'rfid_uid' => ['required', 'string', 'regex:/\A(?:[A-Fa-f0-9]{8}|[A-Fa-f0-9]{10}|[A-Fa-f0-9]{14}|[A-Fa-f0-9]{20})\z/', 'unique:responsaveis,rfid_uid'],
            'acao_permitida' => ['required', Rule::in(['retirada', 'devolucao', 'ambas'])],
        ]);
        $validated['rfid_uid'] = strtoupper(trim($validated['rfid_uid']));

        Responsavel::query()->create($validated);

        return redirect()->route('admin.responsaveis.index')->with('success', 'Responsável cadastrado.');
    }

    public function edit(Responsavel $responsavel): View
    {
        return view('responsaveis.edit', ['responsavel' => $responsavel]);
    }

    public function update(Request $request, Responsavel $responsavel): RedirectResponse
    {
        $request->merge(['rfid_uid' => strtoupper(trim((string) $request->input('rfid_uid', '')))]);
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'acao_permitida' => ['required', Rule::in(['retirada', 'devolucao', 'ambas'])],
            'rfid_uid' => [
                'required',
                'string',
                'regex:/\A(?:[A-Fa-f0-9]{8}|[A-Fa-f0-9]{10}|[A-Fa-f0-9]{14}|[A-Fa-f0-9]{20})\z/',
                Rule::unique('responsaveis', 'rfid_uid')->ignore($responsavel->id),
            ],
        ]);
        $validated['rfid_uid'] = strtoupper(trim($validated['rfid_uid']));

        $responsavel->update($validated);

        return redirect()->route('admin.responsaveis.index')->with('success', 'Responsável atualizado.');
    }

    public function alternarAtivo(Responsavel $responsavel): RedirectResponse
    {
        $responsavel->update(['ativo' => ! $responsavel->ativo]);

        return redirect()->route('admin.responsaveis.index')->with('success', 'Status do responsável atualizado.');
    }
}
