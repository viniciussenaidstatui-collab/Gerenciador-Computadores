<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('services.esp32.device_token');
        $providedToken = (string) $request->header('X-DEVICE-TOKEN');

        if ($expectedToken === '' || $providedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Token do dispositivo nao autorizado.',
            ], 401);
        }

        return $next($request);
    }
}
