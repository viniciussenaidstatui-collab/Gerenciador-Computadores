<?php

use App\Http\Controllers\ComputadorController;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\EnsureValidToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cadastro_usuario', function () {
    return view('cadastro_usuario');
})->name('cadastro_usuario');

Route::get('/login', function () {
    return view('login_usuario');
})->name('login');

Route::post('/login', [LoginController::class, 'autenticar']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware(EnsureValidToken::class);

Route::get('/home', function () {
    return view('home');
})->middleware(EnsureValidToken::class)->name('home');

Route::middleware(EnsureValidToken::class)->group(function () {
    Route::get('/computadores/registrar', [ComputadorController::class, 'create'])
        ->name('computadores.create');
    Route::post('/computadores/registrar', [ComputadorController::class, 'store'])
        ->name('computadores.store');
});
