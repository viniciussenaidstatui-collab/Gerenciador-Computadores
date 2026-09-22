<?php

use App\Http\Controllers\ComputadorController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/cadastro_usuario', [UsuarioController::class, 'cadastro_usuario']);
Route::post('/login', [LoginController::class, 'autenticar'])->name('login');
Route::get('/token/validate', [LoginController::class, 'validarToken']);
Route::post('/logout', [LoginController::class, 'logoutApi']);
Route::post('/computadores', [ComputadorController::class, 'storeApi']);
