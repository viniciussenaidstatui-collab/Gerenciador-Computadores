<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidBearerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        return app(LoggadoMiddleware::class)->handle($request, $next);
    }
}
