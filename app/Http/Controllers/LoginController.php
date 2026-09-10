<?php

namespace App\Http\Controllers;

use App\Models\TokenUsuario;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function formulario()
    {
        return view('login_usuario');
    }

    public function autenticar(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required|string',
        ]);

        $usuario = Usuario::where('email', $request->input('email'))->first();

        if (! $usuario || ! Hash::check($request->input('senha'), $usuario->senha)) {
            return response()->json([
                'erro' => 's',
                'mensagem' => 'E-mail ou senha inválidos.',
            ], 200);
        }

        $request->session()->regenerate();
        $request->session()->put('usuario_id', $usuario->id);

        $tokenUsuario = new TokenUsuario;
        $tokenUsuario->usuario_id = $usuario->id;
        $tokenUsuario->token = bin2hex(random_bytes(32));
        $tokenUsuario->valido_ate = now()->addDays(2);
        $tokenUsuario->save();
        $request->session()->put('token_usuario', $tokenUsuario->token);

        return response()->json([
            'erro' => 'n',
            'mensagem' => 'Login realizado com sucesso!',
            'token' => $tokenUsuario->token,
            'redirect' => route('home'),
        ], 200);
    }

    public function validarToken(Request $request)
    {
        $token = $request->bearerToken();

        $tokenValido = $token && TokenUsuario::where('token', $token)
            ->where('valido_ate', '>', now())
            ->exists();

        if (! $tokenValido) {
            return response()->json([
                'mensagem' => 'Token inválido ou expirado.',
            ], 401);
        }

        return response()->json(['valido' => true]);
    }

    public function logout(Request $request)
    {
        $token = $request->session()->pull('token_usuario');

        if ($token) {
            TokenUsuario::where('token', $token)->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'mensagem' => 'Logout realizado com sucesso.',
        ]);
    }
}
