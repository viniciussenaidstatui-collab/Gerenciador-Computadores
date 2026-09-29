<?php

use App\Http\Controllers\ComputadorController;
use App\Http\Controllers\EmprestimoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsuarioController;
use App\Http\Middleware\LoggadoMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/cadastro_usuario', [UsuarioController::class, 'cadastro_usuario']);
Route::post('/login', [LoginController::class, 'autenticar'])->name('login');
Route::get('/token/validate', [LoginController::class, 'validarToken']);
Route::post('/logout', [LoginController::class, 'logoutApi']);

Route::middleware(LoggadoMiddleware::class)->group(function () {
    Route::get('/computadores', [ComputadorController::class, 'indexApi']);
    Route::post('/computadores', [ComputadorController::class, 'storeApi']);
    Route::post('/emprestimos', [EmprestimoController::class, 'storeApi']);
    Route::get('/emprestimos/ativos', [EmprestimoController::class, 'listarAtivosApi']);
    Route::get('/emprestimos/historico', [EmprestimoController::class, 'historicoApi']);
    Route::post('/emprestimos/{emprestimo}/devolver', [EmprestimoController::class, 'devolverApi']);
});
