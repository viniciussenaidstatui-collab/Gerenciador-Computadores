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
            'numero' => ['required', 'integer', 'unique:computadores,numero'],
            'senha_confirmacao' => ['required', 'string'],
        ], [
            'numero.unique' => 'Este número de computador já está registrado.',
            'senha_confirmacao.required' => 'Informe sua senha para confirmar o registro.',
        ]);

        $usuario = $this->usuarioAutenticado($request);

        if (! Hash::check($validated['senha_confirmacao'], $usuario->senha)) {
            return back()
                ->withInput($request->except('senha_confirmacao'))
                ->withErrors(['senha_confirmacao' => 'A senha informada está incorreta.']);
        }

        Computador::query()->create([
            'numero' => $validated['numero'],
            'usuario_id' => $usuario->id,
        ]);

        return redirect()
            ->route('home')
            ->with('success', 'Computador registrado com sucesso.');
    }

    private function usuarioAutenticado(Request $request): Usuario
    {
        return Usuario::query()->findOrFail($request->session()->get('usuario_id'));
    }
}
