<?php

namespace App\Http\Controllers;

use App\Models\Computador;
use App\Models\Emprestimo;
use App\Models\Responsavel;
use App\Models\Usuario;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RfidController extends Controller
{
    private const SELECTION_CACHE_KEY = 'rfid.selecao';

    private const SELECTION_LOCK_KEY = 'rfid.selecao.lock';

    public function selecionarComputador(Request $request, Computador $computador): JsonResponse
    {
        $usuarioId = (int) $request->attributes->get('usuario')->id;

        try {
            $resultado = Cache::lock(self::SELECTION_LOCK_KEY, 10)->block(2, function () use ($computador, $usuarioId): array {
                $selecaoAtual = Cache::get(self::SELECTION_CACHE_KEY);

                if ($selecaoAtual && (int) $selecaoAtual['usuario_id'] !== $usuarioId) {
                    return [
                        'http' => 409,
                        'sucesso' => false,
                        'mensagem' => 'Aguarde, outra seleção em andamento.',
                    ];
                }

                Cache::put(self::SELECTION_CACHE_KEY, [
                    'computador_id' => $computador->id,
                    'usuario_id' => $usuarioId,
                ], now()->addSeconds(60));
                Cache::forget($this->resultadoCacheKey($usuarioId));

                return [
                    'http' => 200,
                    'sucesso' => true,
                    'mensagem' => 'Aproxime o cartão do responsável. Você tem 60 segundos.',
                ];
            });
        } catch (LockTimeoutException) {
            $resultado = [
                'http' => 409,
                'sucesso' => false,
                'mensagem' => 'Aguarde, outra seleção em andamento.',
            ];
        }

        $httpStatus = $resultado['http'];
        unset($resultado['http']);

        return response()->json($resultado, $httpStatus);
    }

    public function status(Request $request): JsonResponse
    {
        $usuarioId = (int) $request->attributes->get('usuario')->id;
        $selecaoAtual = Cache::get(self::SELECTION_CACHE_KEY);

        return response()->json([
            'pendente' => (bool) ($selecaoAtual && (int) $selecaoAtual['usuario_id'] === $usuarioId),
            'resultado' => Cache::pull($this->resultadoCacheKey($usuarioId)),
        ]);
    }

    public function ler(Request $request): JsonResponse
    {
        $uid = strtoupper(trim((string) $request->input('UID', '')));

        if ($uid === '' || ! preg_match('/\A(?:[A-F0-9]{8}|[A-F0-9]{10}|[A-F0-9]{14}|[A-F0-9]{20})\z/', $uid)) {
            return $this->responderRfid(false, null, 'UID ausente ou inválido.', 422);
        }

        $responsavel = Responsavel::query()
            ->where('rfid_uid', $uid)
            ->where('ativo', true)
            ->first();

        if (! $responsavel) {
            $this->guardarResultadoParaSelecao([
                'sucesso' => false,
                'mensagem' => 'Cartão não cadastrado ou responsável inativo.',
            ]);

            return $this->responderRfid(false, null, 'Cartão não cadastrado ou responsável inativo.', 404);
        }

        try {
            $resultado = Cache::lock(self::SELECTION_LOCK_KEY, 10)->block(2, function () use ($responsavel): array {
                $selecao = Cache::get(self::SELECTION_CACHE_KEY);

                if (! $selecao) {
                    return [
                        'http' => 409,
                        'sucesso' => false,
                        'mensagem' => 'Nenhum computador selecionado no site ou seleção expirada.',
                    ];
                }

                $resultado = DB::transaction(function () use ($responsavel, $selecao): array {
                    $responsavelAtivo = Responsavel::query()
                        ->whereKey($responsavel->id)
                        ->where('ativo', true)
                        ->lockForUpdate()
                        ->first();

                    if (! $responsavelAtivo) {
                        return [
                            'http' => 404,
                            'sucesso' => false,
                            'mensagem' => 'Cartão não cadastrado ou responsável inativo.',
                        ];
                    }

                    $computador = Computador::query()
                        ->whereKey($selecao['computador_id'])
                        ->lockForUpdate()
                        ->first();
                    $usuario = Usuario::query()->find($selecao['usuario_id']);

                    if (! $computador || ! $usuario) {
                        return [
                            'http' => 409,
                            'sucesso' => false,
                            'mensagem' => 'A seleção expirou. Escolha o computador novamente.',
                        ];
                    }

                    $emprestimoAtivo = Emprestimo::query()
                        ->where('computador_id', $computador->id)
                        ->whereNull('devolvido_em')
                        ->lockForUpdate()
                        ->first();

                    $acaoNecessaria = $emprestimoAtivo ? 'devolucao' : 'retirada';

                    if ($responsavelAtivo->acao_permitida !== 'ambas' && $responsavelAtivo->acao_permitida !== $acaoNecessaria) {
                        return [
                            'http' => 409,
                            'sucesso' => false,
                            'mensagem' => $responsavelAtivo->acao_permitida === 'retirada'
                                ? 'Este cartao autoriza somente retirada.'
                                : 'Este cartao autoriza somente devolucao.',
                        ];
                    }

                    if ($emprestimoAtivo) {
                        $emprestimoAtivo->update([
                            'devolvido_em' => now(),
                            'devolucao_autorizada_por' => $responsavelAtivo->id,
                        ]);
                        $computador->update(['status' => 'disponivel']);

                        return [
                            'http' => 200,
                            'sucesso' => true,
                            'acao' => 'devolucao',
                            'mensagem' => "Devolução do {$computador->numero_patrimonio} autorizada por {$responsavelAtivo->nome}.",
                        ];
                    }

                    Emprestimo::query()->create([
                        'computador_id' => $computador->id,
                        'usuario_id' => $usuario->id,
                        'retirado_em' => now(),
                        'retirada_autorizada_por' => $responsavelAtivo->id,
                    ]);
                    $computador->update(['status' => 'em_uso']);

                    return [
                        'http' => 200,
                        'sucesso' => true,
                        'acao' => 'retirada',
                        'mensagem' => "Retirada do {$computador->numero_patrimonio} autorizada por {$responsavelAtivo->nome}.",
                    ];
                });

                if ($resultado['http'] !== 404) {
                    Cache::forget(self::SELECTION_CACHE_KEY);
                }

                Cache::put($this->resultadoCacheKey((int) $selecao['usuario_id']), [
                    'sucesso' => $resultado['sucesso'],
                    'acao' => $resultado['acao'] ?? null,
                    'mensagem' => $resultado['mensagem'],
                ], now()->addSeconds(30));

                return $resultado;
            });
        } catch (LockTimeoutException) {
            $resultado = [
                'http' => 409,
                'sucesso' => false,
                'mensagem' => 'Outra leitura RFID está sendo processada. Tente novamente.',
            ];
        }

        $httpStatus = $resultado['http'];
        unset($resultado['http']);

        return response()->json($resultado, $httpStatus);
    }

    private function guardarResultadoParaSelecao(array $resultado): void
    {
        $selecao = Cache::get(self::SELECTION_CACHE_KEY);

        if ($selecao) {
            Cache::put($this->resultadoCacheKey((int) $selecao['usuario_id']), $resultado, now()->addSeconds(30));
        }
    }

    private function resultadoCacheKey(int $usuarioId): string
    {
        return 'rfid.resultado.'.$usuarioId;
    }

    private function responderRfid(bool $sucesso, ?string $acao, string $mensagem, int $httpStatus): JsonResponse
    {
        $resultado = ['sucesso' => $sucesso];

        if ($acao !== null) {
            $resultado['acao'] = $acao;
        }

        $resultado['mensagem'] = $mensagem;

        return response()->json($resultado, $httpStatus);
    }

    public function create(Request $request): View
    {
        $usuario = Usuario::query()->findOrFail($request->session()->get('usuario_id'));
        $baseUrl = rtrim((string) config('services.esp32.base_url'), '/');

        return view('computadores.rfid', [
            'usuario' => $usuario,
            'esp32Url' => $baseUrl === '' ? null : $baseUrl.'/rfid/gravar',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'numero_patrimonio' => [
                'required',
                'string',
                'max:16',
                'regex:/\A[A-Za-z0-9_-]+\z/',
                'unique:computadores,numero_patrimonio',
            ],
            'senha_confirmacao' => ['required', 'string'],
            'rfid_uid' => [
                'required',
                'string',
                'regex:/\A(?:[A-Fa-f0-9]{8}|[A-Fa-f0-9]{10}|[A-Fa-f0-9]{14}|[A-Fa-f0-9]{20})\z/',
            ],
        ], [
            'numero_patrimonio.max' => 'O patrimônio pode ter no máximo 16 caracteres.',
            'numero_patrimonio.regex' => 'Use somente letras, números, hífen e sublinhado.',
            'numero_patrimonio.unique' => 'Este patrimônio já está registrado.',
            'rfid_uid.regex' => 'O ESP32 retornou um UID de etiqueta inválido.',
        ]);

        $usuario = Usuario::query()->findOrFail($request->session()->get('usuario_id'));

        if (! Hash::check($validated['senha_confirmacao'], $usuario->senha)) {
            return response()->json([
                'message' => 'A senha informada está incorreta.',
                'errors' => ['senha_confirmacao' => ['A senha informada está incorreta.']],
            ], 422);
        }

        $uid = strtoupper($validated['rfid_uid']);

        if (Computador::query()->where('rfid_uid', $uid)->exists()) {
            return response()->json([
                'message' => 'Esta etiqueta RFID já está vinculada a outro computador.',
            ], 409);
        }

        try {
            $computador = DB::transaction(fn (): Computador => Computador::query()->create([
                'numero_patrimonio' => $validated['numero_patrimonio'],
                'rfid_uid' => $uid,
            ]));
        } catch (QueryException) {
            return response()->json([
                'message' => 'Não foi possível salvar o computador. Verifique se o patrimônio ou a etiqueta já estão cadastrados.',
            ], 409);
        }

        return response()->json([
            'message' => 'Etiqueta gravada e computador cadastrado com sucesso.',
            'computador' => [
                'id' => $computador->id,
                'numero_patrimonio' => $computador->numero_patrimonio,
                'rfid_uid' => $computador->rfid_uid,
            ],
        ], 201);
    }
}
