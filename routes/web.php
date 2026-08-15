<?php

use App\Http\Controllers\PayableController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ReceivableController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('pessoas-empresas', PersonController::class)
        ->parameters(['pessoas-empresas' => 'person'])
        ->names('people')
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('contas-receber', ReceivableController::class)
        ->parameters(['contas-receber' => 'receivable'])
        ->names('receivables')
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('contas-pagar', PayableController::class)
        ->parameters(['contas-pagar' => 'payable'])
        ->names('payables')
        ->only(['index', 'store', 'update', 'destroy']);
});