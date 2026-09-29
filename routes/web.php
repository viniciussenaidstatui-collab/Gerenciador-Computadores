<?php

use App\Http\Controllers\ComputadorController;
use App\Http\Controllers\EmprestimoController;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\LoggadoMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cadastro_usuario', function () {
    return view('cadastro_usuario');
})->name('cadastro_usuario');

Route::get('/login', function () {
    return view('login_usuario');
})->name('login_usuario');

Route::post('/login', [LoginController::class, 'autenticar']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware(LoggadoMiddleware::class);

Route::get('/home', function () {
    return view('home');
})->middleware(LoggadoMiddleware::class)->name('home');

Route::middleware(LoggadoMiddleware::class)->group(function () {
    Route::get('/computadores/registrar', [ComputadorController::class, 'create'])
        ->name('computadores.create');
    Route::post('/computadores/registrar', [ComputadorController::class, 'store'])
        ->name('computadores.store');
    Route::get('/emprestimos', [EmprestimoController::class, 'create'])
        ->name('emprestimos.create');
    Route::post('/emprestimos', [EmprestimoController::class, 'store'])
        ->name('emprestimos.store');
    Route::patch('/emprestimos/{emprestimo}/devolver', [EmprestimoController::class, 'devolver'])
        ->name('emprestimos.devolver');
});
