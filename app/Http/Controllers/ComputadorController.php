<?php

namespace App\Http\Controllers;

use App\Models\Computador;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ComputadorController extends Controller
{
    public function create(Request $request): View
    {
        return view('computadores.create', [
            'usuario' => $this->usuarioAutenticado($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero_patrimonio' => ['required', 'string', 'max:255', 'unique:computadores,numero_patrimonio'],
            'senha_confirmacao' => ['required', 'string'],
        ], [
            'numero_patrimonio.unique' => 'Este número de patrimônio já está registrado.',
            'senha_confirmacao.required' => 'Informe sua senha para confirmar o registro.',
        ]);

        $usuario = $this->usuarioAutenticado($request);

        if (! Hash::check($validated['senha_confirmacao'], $usuario->senha)) {
            return back()
                ->withInput($request->except('senha_confirmacao'))
                ->withErrors(['senha_confirmacao' => 'A senha informada está incorreta.']);
        }

        Computador::query()->create([
            'numero_patrimonio' => $validated['numero_patrimonio'],
        ]);

        return redirect()
            ->route('home')
            ->with('success', 'Computador registrado com sucesso.');
    }

    public function storeApi(Request $request)
    {
        $usuario = $request->attributes->get('usuario');

        if (! $usuario) {
            return response()->json(['mensagem' => 'Usuário não encontrado.'], 401);
        }

        $validated = $request->validate([
            'numero_patrimonio' => ['required', 'string', 'max:255', 'unique:computadores,numero_patrimonio'],
            'senha_confirmacao' => ['required', 'string'],
        ], [
            'numero_patrimonio.unique' => 'Este número de patrimônio já está registrado.',
            'senha_confirmacao.required' => 'Informe sua senha para confirmar o registro.',
        ]);

        if (! Hash::check($validated['senha_confirmacao'], $usuario->senha)) {
            return response()->json([
                'mensagem' => 'A senha informada está incorreta.',
            ], 422);
        }

        $computador = Computador::query()->create([
            'numero_patrimonio' => $validated['numero_patrimonio'],
        ]);

        return response()->json([
            'mensagem' => 'Computador registrado com sucesso.',
            'computador' => $computador,
        ], 201);
    }

    public function indexApi(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:disponivel,em_uso'],
        ]);

        $computadores = Computador::query()
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('numero_patrimonio')
            ->get();

        return response()->json(['computadores' => $computadores]);
    }

    private function usuarioAutenticado(Request $request): Usuario
    {
        return Usuario::query()->findOrFail($request->session()->get('usuario_id'));
    }
}
