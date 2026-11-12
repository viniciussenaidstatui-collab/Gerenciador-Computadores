<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('rfid-reader', fn (Request $request): Limit => Limit::perMinute(30)
            ->by($request->ip())
            ->response(fn (Request $request, array $headers): JsonResponse => response()->json([
                'sucesso' => false,
                'mensagem' => 'Muitas leituras em pouco tempo. Tente novamente em instantes.',
            ], 429, $headers)));
    }
}
