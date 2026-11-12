<?php

namespace App\Http\Middleware;

use App\Models\TokenUsuario;
use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoggadoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('token_usuario');
        $tokenUsuario = $token
            ? TokenUsuario::query()
                ->where('token', $token)
                ->where('valido_ate', '>', now())
                ->first()
            : null;

        if (! $tokenUsuario) {
            if ($request->is('api/*')) {
                return response()->json(['mensagem' => 'Token inválido ou expirado.'], 401);
            }

            $request->session()->forget('usuario_id');

            return redirect()->route('login_usuario');
        }

        $usuario = Usuario::query()->find($tokenUsuario->usuario_id);

        if (! $usuario) {
            if ($request->is('api/*')) {
                return response()->json(['mensagem' => 'Usuário não encontrado.'], 401);
            }

            $request->session()->forget('usuario_id');

            return redirect()->route('login_usuario');
        }

        $request->attributes->set('usuario', $usuario);

        if (! $request->is('api/*')) {
            $request->session()->put('usuario_id', $usuario->id);
        }

        return $next($request);
    }
}
