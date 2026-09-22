<?php

namespace App\Http\Controllers;

use App\Models\Computador;
use App\Models\Emprestimo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmprestimoController extends Controller
{
    public function create(Request $request): View
    {
        $usuario = $this->usuarioAutenticado($request);

        return view('emprestimos.create', [
            'computadoresDisponiveis' => Computador::query()
                ->where('status', 'disponivel')
                ->orderBy('numero_patrimonio')
                ->get(),
            'emprestimosAtivos' => Emprestimo::query()
                ->with('computador')
                ->whereBelongsTo($usuario)
                ->whereNull('devolvido_em')
                ->latest('retirado_em')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'computador_id' => ['required', 'integer', 'exists:computadores,id'],
        ]);
        $usuario = $this->usuarioAutenticado($request);

        try {
            DB::transaction(function () use ($validated, $usuario): void {
                $computador = Computador::query()
                    ->whereKey($validated['computador_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $computador || $computador->status !== 'disponivel') {
                    throw ValidationException::withMessages([
                        'computador_id' => 'Este computador não está disponível para retirada.',
                    ]);
                }

                Emprestimo::query()->create([
                    'computador_id' => $computador->id,
                    'usuario_id' => $usuario->id,
                    'retirado_em' => now(),
                ]);

                $computador->update(['status' => 'em_uso']);
            });
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()
            ->route('emprestimos.create')
            ->with('success', 'Computador retirado com sucesso.');
    }

    public function devolver(Request $request, Emprestimo $emprestimo): RedirectResponse
    {
        $usuario = $this->usuarioAutenticado($request);

        try {
            DB::transaction(function () use ($emprestimo, $usuario): void {
                $emprestimo = Emprestimo::query()
                    ->whereKey($emprestimo->id)
                    ->whereBelongsTo($usuario)
                    ->whereNull('devolvido_em')
                    ->lockForUpdate()
                    ->firstOrFail();
                $computador = Computador::query()
                    ->whereKey($emprestimo->computador_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $emprestimo->update(['devolvido_em' => now()]);
                $computador->update(['status' => 'disponivel']);
            });
        } catch (ModelNotFoundException) {
            abort(404);
        }

        return redirect()
            ->route('emprestimos.create')
            ->with('success', 'Computador devolvido com sucesso.');
    }

    private function usuarioAutenticado(Request $request): Usuario
    {
        return Usuario::query()->findOrFail($request->session()->get('usuario_id'));
    }
}
