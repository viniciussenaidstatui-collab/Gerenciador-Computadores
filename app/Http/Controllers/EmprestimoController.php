<?php

namespace App\Http\Controllers;

use App\Models\Computador;
use App\Models\Emprestimo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmprestimoController extends Controller
{
    public function create(Request $request): View
    {
        return view('emprestimos.create', [
            'computadores' => Computador::query()
                ->with(['emprestimos' => function (HasMany $query): void {
                    $query->whereNull('devolvido_em')->with('usuario');
                }])
                ->orderBy('numero_patrimonio')
                ->get(),
        ]);
    }

    public function historico(): View
    {
        return view('emprestimos.historico', [
            'emprestimos' => Emprestimo::query()
                ->with(['computador', 'usuario', 'retiradaAutorizadaPor', 'devolucaoAutorizadaPor'])
                ->latest('retirado_em')
                ->paginate(50),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->merge([
            'usuario_rfid_uid' => $this->normalizarUid($request->input('usuario_rfid_uid')),
        ]);

        $validated = $request->validate([
            'computador_id' => ['required', 'integer', 'exists:computadores,id'],
            'usuario_rfid_uid' => ['required', 'string', 'regex:/\A(?:[A-F0-9]{8}|[A-F0-9]{10}|[A-F0-9]{14}|[A-F0-9]{20})\z/'],
        ]);

        $usuario = Usuario::query()
            ->where('rfid_uid', $validated['usuario_rfid_uid'])
            ->first();

        if (! $usuario) {
            return $this->responderEmprestimoFalho(
                $request,
                'usuario_rfid_uid',
                'Este cartão RFID não está cadastrado para nenhum usuário.',
                422,
            );
        }

        $resultado = DB::transaction(function () use ($validated, $usuario): array {
            $computador = Computador::query()
                ->whereKey($validated['computador_id'])
                ->lockForUpdate()
                ->first();

            if (! $computador || $computador->status !== 'disponivel') {
                return ['status' => 'computador_indisponivel'];
            }

            $emprestimo = Emprestimo::query()->create([
                'computador_id' => $computador->id,
                'usuario_id' => $usuario->id,
                'retirado_em' => now(),
            ]);

            $computador->update(['status' => 'em_uso']);

            return [
                'status' => 'sucesso',
                'computador' => $computador,
                'emprestimo' => $emprestimo,
            ];
        });

        if ($resultado['status'] !== 'sucesso') {
            return $this->responderEmprestimoFalho(
                $request,
                'computador_id',
                'Este computador não está mais disponível. Escolha outro computador.',
                409,
            );
        }

        if (! $request->expectsJson()) {
            return redirect()
                ->route('emprestimos.create')
                ->with('success', 'Empréstimo registrado com sucesso.');
        }

        return response()->json([
            'mensagem' => 'Empréstimo registrado com sucesso.',
            'usuario' => ['nome' => $usuario->nome],
            'computador' => [
                'id' => $resultado['computador']->id,
                'numero_patrimonio' => $resultado['computador']->numero_patrimonio,
            ],
            'emprestimo' => [
                'id' => $resultado['emprestimo']->id,
                'retirado_em' => $resultado['emprestimo']->retirado_em?->toIso8601String(),
            ],
        ], 201);
    }

    public function iniciarEmprestimoRfid(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'computador_id' => ['required', 'integer', 'exists:computadores,id'],
        ]);
        $usuarioSolicitante = $this->usuarioAutenticado($request);

        $operacaoId = DB::transaction(function () use ($validated, $usuarioSolicitante): int {
            $computador = Computador::query()
                ->whereKey($validated['computador_id'])
                ->lockForUpdate()
                ->first();

            if (! $computador || $computador->status !== 'disponivel') {
                throw ValidationException::withMessages([
                    'computador_id' => 'Este computador não está disponível para retirada.',
                ]);
            }

            DB::table('rfid_emprestimo_operacoes')
                ->where('status', 'aguardando')
                ->update([
                    'status' => 'cancelada',
                    'mensagem' => 'Uma nova leitura RFID foi iniciada.',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            return (int) DB::table('rfid_emprestimo_operacoes')->insertGetId([
                'computador_id' => $computador->id,
                'solicitado_por_usuario_id' => $usuarioSolicitante->id,
                'status' => 'aguardando',
                'mensagem' => 'Aproxime o cartão RFID do usuário.',
                'expires_at' => now()->addMinute(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json([
            'operacao_id' => $operacaoId,
            'status' => 'aguardando',
            'mensagem' => 'Aproxime o cartão RFID do usuário.',
        ], 201);
    }

    public function statusEmprestimoRfid(Request $request, int $operacao): JsonResponse
    {
        $usuarioSolicitante = $this->usuarioAutenticado($request);
        $operacaoRfid = DB::table('rfid_emprestimo_operacoes')
            ->where('id', $operacao)
            ->where('solicitado_por_usuario_id', $usuarioSolicitante->id)
            ->first();

        if (! $operacaoRfid) {
            abort(404);
        }

        if ($operacaoRfid->status === 'aguardando' && now()->greaterThanOrEqualTo($operacaoRfid->expires_at)) {
            DB::table('rfid_emprestimo_operacoes')
                ->where('id', $operacaoRfid->id)
                ->where('status', 'aguardando')
                ->update([
                    'status' => 'expirada',
                    'mensagem' => 'Tempo esgotado. Inicie uma nova leitura.',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            $operacaoRfid->status = 'expirada';
            $operacaoRfid->mensagem = 'Tempo esgotado. Inicie uma nova leitura.';
        }

        $resultado = [
            'status' => $operacaoRfid->status,
            'mensagem' => $operacaoRfid->mensagem,
        ];

        if ($operacaoRfid->status === 'concluida') {
            $usuario = Usuario::query()->find($operacaoRfid->usuario_id);
            $computador = Computador::query()->find($operacaoRfid->computador_id);
            $resultado['usuario'] = ['nome' => $usuario?->nome];
            $resultado['computador'] = ['numero_patrimonio' => $computador?->numero_patrimonio];
        }

        return response()->json($resultado);
    }

    public function receberUidRfid(Request $request): JsonResponse
    {
        $uid = $this->normalizarUid($request->input('UID'));

        if (! preg_match('/\A(?:[A-F0-9]{8}|[A-F0-9]{10}|[A-F0-9]{14}|[A-F0-9]{20})\z/', $uid)) {
            Log::info('RFID UID recebido', [
                'uid' => $uid,
                'ip' => $request->ip(),
                'resultado' => 'uid_invalido',
            ]);

            return response()->json([
                'status' => 'uid_invalido',
                'led' => 'vermelho',
                'mensagem' => 'UID RFID inválido.',
            ], 422);
        }

        $resultado = DB::transaction(function () use ($uid): array {
            $agora = now();
            DB::table('rfid_emprestimo_operacoes')
                ->where('status', 'aguardando')
                ->where('expires_at', '<=', $agora)
                ->update([
                    'status' => 'expirada',
                    'mensagem' => 'Tempo esgotado. Inicie uma nova leitura.',
                    'completed_at' => $agora,
                    'updated_at' => $agora,
                ]);

            $operacao = DB::table('rfid_emprestimo_operacoes')
                ->where('status', 'aguardando')
                ->where('expires_at', '>', $agora)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (! $operacao) {
                return [
                    'http' => 409,
                    'status' => 'sem_operacao',
                    'led' => 'vermelho',
                    'mensagem' => 'Selecione um computador no site antes de ler o cartão.',
                ];
            }

            $usuario = Usuario::query()->where('rfid_uid', $uid)->first();

            if (! $usuario) {
                $mensagem = 'Este cartão não está cadastrado para nenhum usuário.';
                $this->finalizarOperacaoRfid((int) $operacao->id, 'negada', $mensagem);

                return ['http' => 403, 'status' => 'usuario_nao_autorizado', 'led' => 'vermelho', 'mensagem' => $mensagem];
            }

            $computador = Computador::query()
                ->whereKey($operacao->computador_id)
                ->lockForUpdate()
                ->first();

            if (! $computador || $computador->status !== 'disponivel') {
                $mensagem = 'Este computador não está mais disponível.';
                $this->finalizarOperacaoRfid((int) $operacao->id, 'falhou', $mensagem);

                return ['http' => 409, 'status' => 'computador_indisponivel', 'led' => 'vermelho', 'mensagem' => $mensagem];
            }

            Emprestimo::query()->create([
                'computador_id' => $computador->id,
                'usuario_id' => $usuario->id,
                'retirado_em' => $agora,
            ]);

            $computador->update(['status' => 'em_uso']);
            $mensagem = 'Empréstimo registrado para '.$usuario->nome.'.';
            $this->finalizarOperacaoRfid((int) $operacao->id, 'concluida', $mensagem, $usuario->id);

            return ['http' => 201, 'status' => 'sucesso', 'led' => 'verde', 'mensagem' => $mensagem];
        });

        Log::info('RFID UID recebido', [
            'uid' => $uid,
            'ip' => $request->ip(),
            'resultado' => $resultado['status'],
            'mensagem' => $resultado['mensagem'],
            'http_status' => $resultado['http'],
        ]);

        $statusCode = $resultado['http'];
        unset($resultado['http']);

        return response()->json($resultado, $statusCode);
    }

    private function finalizarOperacaoRfid(int $operacaoId, string $status, string $mensagem, ?int $usuarioId = null): void
    {
        DB::table('rfid_emprestimo_operacoes')
            ->where('id', $operacaoId)
            ->update([
                'usuario_id' => $usuarioId,
                'status' => $status,
                'mensagem' => $mensagem,
                'completed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function storeRfid(Request $request): JsonResponse
    {
        $validated = $this->validarUids($request);
        $usuario = Usuario::query()->where('rfid_uid', $validated['usuario_rfid_uid'])->first();

        if (! $usuario) {
            return response()->json([
                'status' => 'usuario_nao_autorizado',
                'mensagem' => 'RFID do usuário não autorizado.',
            ], 403);
        }

        $resultado = DB::transaction(function () use ($validated, $usuario): array {
            $computador = Computador::query()
                ->where('rfid_uid', $validated['computador_rfid_uid'])
                ->lockForUpdate()
                ->first();

            if (! $computador) {
                return ['status' => 'computador_nao_encontrado'];
            }

            if ($computador->status !== 'disponivel') {
                return ['status' => 'computador_indisponivel'];
            }

            $emprestimo = Emprestimo::query()->create([
                'computador_id' => $computador->id,
                'usuario_id' => $usuario->id,
                'retirado_em' => now(),
            ]);

            $computador->update(['status' => 'em_uso']);

            return ['status' => 'sucesso', 'computador' => $computador, 'emprestimo' => $emprestimo];
        });

        if ($resultado['status'] === 'computador_nao_encontrado') {
            return response()->json([
                'status' => $resultado['status'],
                'mensagem' => 'RFID do computador não cadastrado.',
            ], 404);
        }

        if ($resultado['status'] === 'computador_indisponivel') {
            return response()->json([
                'status' => $resultado['status'],
                'mensagem' => 'Este computador já está em uso.',
            ], 409);
        }

        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Empréstimo autorizado.',
            'usuario' => ['id' => $usuario->id, 'nome' => $usuario->nome],
            'computador' => [
                'id' => $resultado['computador']->id,
                'numero_patrimonio' => $resultado['computador']->numero_patrimonio,
                'rfid_uid' => $resultado['computador']->rfid_uid,
            ],
            'emprestimo' => [
                'id' => $resultado['emprestimo']->id,
                'retirado_em' => $resultado['emprestimo']->retirado_em?->toIso8601String(),
            ],
        ], 201);
    }

    public function devolverRfid(Request $request): JsonResponse
    {
        $validated = $this->validarUids($request);
        $usuario = Usuario::query()->where('rfid_uid', $validated['usuario_rfid_uid'])->first();

        if (! $usuario) {
            return response()->json([
                'status' => 'usuario_nao_autorizado',
                'mensagem' => 'RFID do usuário não autorizado.',
            ], 403);
        }

        $resultado = DB::transaction(function () use ($validated, $usuario): string {
            $computador = Computador::query()
                ->where('rfid_uid', $validated['computador_rfid_uid'])
                ->lockForUpdate()
                ->first();

            if (! $computador) {
                return 'computador_nao_encontrado';
            }

            $emprestimo = Emprestimo::query()
                ->where('computador_id', $computador->id)
                ->where('usuario_id', $usuario->id)
                ->whereNull('devolvido_em')
                ->lockForUpdate()
                ->first();

            if (! $emprestimo) {
                return 'emprestimo_nao_encontrado';
            }

            $emprestimo->update(['devolvido_em' => now()]);
            $computador->update(['status' => 'disponivel']);

            return 'sucesso';
        });

        if ($resultado !== 'sucesso') {
            $statusCode = $resultado === 'computador_nao_encontrado' ? 404 : 409;

            return response()->json([
                'status' => $resultado,
                'mensagem' => $resultado === 'computador_nao_encontrado'
                    ? 'RFID do computador não cadastrado.'
                    : 'Não existe empréstimo ativo deste computador para este usuário.',
            ], $statusCode);
        }

        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Devolução realizada.',
        ]);
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

    private function responderEmprestimoFalho(
        Request $request,
        string $campo,
        string $mensagem,
        int $statusCode,
    ): JsonResponse|RedirectResponse {
        if (! $request->expectsJson()) {
            return back()->withErrors([$campo => $mensagem]);
        }

        return response()->json([
            'message' => $mensagem,
            'errors' => [$campo => [$mensagem]],
        ], $statusCode);
    }

    /**
     * @return array{usuario_rfid_uid: string, computador_rfid_uid: string}
     */
    private function validarUids(Request $request): array
    {
        $request->merge([
            'usuario_rfid_uid' => $this->normalizarUid($request->input('usuario_rfid_uid')),
            'computador_rfid_uid' => $this->normalizarUid($request->input('computador_rfid_uid')),
        ]);

        return $request->validate([
            'usuario_rfid_uid' => ['required', 'string', 'regex:/\A(?:[A-F0-9]{8}|[A-F0-9]{10}|[A-F0-9]{14}|[A-F0-9]{20})\z/'],
            'computador_rfid_uid' => ['required', 'string', 'regex:/\A(?:[A-F0-9]{8}|[A-F0-9]{10}|[A-F0-9]{14}|[A-F0-9]{20})\z/'],
        ]);
    }

    private function normalizarUid(mixed $uid): string
    {
        return strtoupper((string) preg_replace('/[^A-Fa-f0-9]/', '', (string) $uid));
    }
}
