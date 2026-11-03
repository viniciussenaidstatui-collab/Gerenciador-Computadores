<?php

namespace App\Http\Controllers;

use App\Models\Computador;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RfidController extends Controller
{
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
                'regex:/\A(?:[A-Fa-f0-9]{8}|[A-Fa-f0-9]{14}|[A-Fa-f0-9]{20})\z/',
            ],
            'status_esp32' => ['required', 'in:sucesso'],
            'patrimonio_esp32' => ['required', 'same:numero_patrimonio'],
        ], [
            'numero_patrimonio.max' => 'O patrimônio pode ter no máximo 16 caracteres.',
            'numero_patrimonio.regex' => 'Use somente letras, números, hífen e sublinhado.',
            'numero_patrimonio.unique' => 'Este patrimônio já está registrado.',
            'rfid_uid.regex' => 'O ESP32 retornou um UID de etiqueta inválido.',
            'status_esp32.in' => 'O ESP32 não confirmou a gravação.',
            'patrimonio_esp32.same' => 'O patrimônio confirmado pela etiqueta é diferente do informado.',
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
