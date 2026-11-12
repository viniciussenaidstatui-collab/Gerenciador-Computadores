<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function cadastro_usuario(Request $request)
    {

        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required',
            'senha' => 'required|string|min:6',
            'cpf' => 'required|string|max:11',
            'data_nascimento' => 'required',
            'rfid_uid' => ['nullable', 'string', 'regex:/\A(?:[A-Fa-f0-9]{8}|[A-Fa-f0-9]{10}|[A-Fa-f0-9]{14}|[A-Fa-f0-9]{20})\z/', 'unique:usuario,rfid_uid'],
        ]);

        $usuario = new Usuario;

        if ($usuario->where('email', '=', $request->email)->exists()) {
            return response()->json(['erro' => 's', 'mensagem' => 'Email já cadastrado!'], 200);

        }

        try {
            $usuario->nome = $request->nome;
            $usuario->email = $request->email;
            $usuario->senha = bcrypt($request->senha);
            $usuario->cpf = $request->cpf;
            $usuario->data_nascimento = $request->data_nascimento;
            $usuario->rfid_uid = $request->filled('rfid_uid')
                ? strtoupper($request->string('rfid_uid')->toString())
                : null;
            $usuario->save();

            return response()->json(['erro' => 'n', 'mensagem' => 'Usuário cadastrado com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json(['erro' => 's', 'mensagem' => 'Erro ao cadastrar usuário: '.$e->getMessage()], 500);
        }

    }
}
