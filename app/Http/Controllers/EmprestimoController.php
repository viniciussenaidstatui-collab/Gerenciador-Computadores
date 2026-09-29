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

    public function storeApi(Request $request)
    {
        $validated = $request->validate([
            'computador_id' => ['required', 'integer', 'exists:computadores,id'],
        ]);
        $usuario = $this->usuarioApiAutenticado($request);

        $emprestimo = DB::transaction(function () use ($validated, $usuario): Emprestimo {
            $computador = Computador::query()
                ->whereKey($validated['computador_id'])
                ->lockForUpdate()
                ->first();

            if (! $computador || $computador->status !== 'disponivel') {
                throw ValidationException::withMessages([
                    'computador_id' => 'Este computador não está disponível para retirada.',
                ]);
            }

            $emprestimo = Emprestimo::query()->create([
                'computador_id' => $computador->id,
                'usuario_id' => $usuario->id,
                'retirado_em' => now(),
            ]);

            $computador->update(['status' => 'em_uso']);

            return $emprestimo->load('computador');
        });

        return response()->json([
            'mensagem' => 'Computador retirado com sucesso.',
            'emprestimo' => $emprestimo,
        ], 201);
    }

    public function devolverApi(Request $request, Emprestimo $emprestimo)
    {
        $usuario = $this->usuarioApiAutenticado($request);

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
            return response()->json([
                'mensagem' => 'Empréstimo não encontrado ou já devolvido.',
            ], 404);
        }

        return response()->json(['mensagem' => 'Computador devolvido com sucesso.']);
    }

    public function listarAtivosApi(Request $request)
    {
        $usuario = $this->usuarioApiAutenticado($request);
        $emprestimos = Emprestimo::query()
            ->with('computador')
            ->whereBelongsTo($usuario)
            ->whereNull('devolvido_em')
            ->latest('retirado_em')
            ->get();

        return response()->json(['emprestimos' => $emprestimos]);
    }

    public function historicoApi(Request $request)
    {
        $usuario = $this->usuarioApiAutenticado($request);
        $emprestimos = Emprestimo::query()
            ->with('computador')
            ->whereBelongsTo($usuario)
            ->latest('retirado_em')
            ->get();

        return response()->json(['emprestimos' => $emprestimos]);
    }

    private function usuarioAutenticado(Request $request): Usuario
    {
        return Usuario::query()->findOrFail($request->session()->get('usuario_id'));
    }

    private function usuarioApiAutenticado(Request $request): Usuario
    {
        return $request->attributes->get('usuario');
    }
}
