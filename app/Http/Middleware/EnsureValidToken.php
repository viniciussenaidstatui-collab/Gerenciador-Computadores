<?php

namespace App\Http\Middleware;

use App\Models\TokenUsuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->session()->get('token_usuario');

        $tokenValido = $token && TokenUsuario::where('token', $token)
            ->where('valido_ate', '>', now())
            ->exists();

        if (! $tokenValido) {
            $request->session()->forget(['usuario_id', 'token_usuario']);

            return redirect()->route('login');
        }

        return $next($request);
    }
}
