<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialEntryController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\PersonController;
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
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('pessoas-empresas', PersonController::class)
        ->parameters(['pessoas-empresas' => 'person'])
        ->names('people')
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('contas', FinancialEntryController::class)
        ->parameters(['contas' => 'financialEntry'])
        ->names('financial-entries')
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/relatorios', FinancialReportController::class)->name('reports.index');
});