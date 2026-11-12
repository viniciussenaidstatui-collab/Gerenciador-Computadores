<?php

use App\Http\Controllers\ComputadorController;
use App\Http\Controllers\EmprestimoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ResponsavelController;
use App\Http\Controllers\RfidController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\LoggadoMiddleware;
use Illuminate\Http\Request;
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

Route::get('/led', function () {
    return view('Led');
})->name('led');

Route::post('/login', [LoginController::class, 'autenticar']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware(LoggadoMiddleware::class);

Route::get('/home', function (Request $request) {
    return view('home', ['usuario' => $request->attributes->get('usuario')]);
})->middleware(LoggadoMiddleware::class)->name('home');

Route::middleware(LoggadoMiddleware::class)->group(function () {
    Route::get('/computadores/registrar', [ComputadorController::class, 'create'])
        ->name('computadores.create');
    Route::post('/computadores/registrar', [ComputadorController::class, 'store'])
        ->name('computadores.store');
    Route::get('/computadores/etiqueta', [RfidController::class, 'create'])
        ->name('computadores.rfid.create');
    Route::post('/computadores/etiqueta', [RfidController::class, 'store'])
        ->name('computadores.rfid.store');
    Route::get('/emprestimos', [EmprestimoController::class, 'create'])
        ->name('emprestimos.create');
    Route::get('/emprestimos/historico', [EmprestimoController::class, 'historico'])
        ->name('emprestimos.historico');
    Route::post('/computadores/{computador}/selecionar', [RfidController::class, 'selecionarComputador'])
        ->name('computadores.selecionar');
    Route::get('/rfid/status', [RfidController::class, 'status'])
        ->name('rfid.status');
    Route::post('/emprestimos', [EmprestimoController::class, 'store'])
        ->name('emprestimos.store');
    Route::patch('/emprestimos/{emprestimo}/devolver', [EmprestimoController::class, 'devolver'])
        ->name('emprestimos.devolver');

    Route::middleware(AdminMiddleware::class)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/responsaveis', [ResponsavelController::class, 'index'])->name('responsaveis.index');
        Route::post('/responsaveis', [ResponsavelController::class, 'store'])->name('responsaveis.store');
        Route::get('/responsaveis/{responsavel}/editar', [ResponsavelController::class, 'edit'])->name('responsaveis.edit');
        Route::put('/responsaveis/{responsavel}', [ResponsavelController::class, 'update'])->name('responsaveis.update');
        Route::patch('/responsaveis/{responsavel}/ativo', [ResponsavelController::class, 'alternarAtivo'])->name('responsaveis.ativo');
    });
});
