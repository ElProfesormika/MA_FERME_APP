<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\LogController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'statsParPeriode'])->name('dashboard.stats');
    Route::get('/dashboard/rapport-pdf', [DashboardController::class, 'rapportPDF'])->name('dashboard.pdf');

    // Ressources
    Route::resource('animaux', AnimalController::class);
    Route::resource('stocks', StockController::class);
    Route::resource('employes', EmployeController::class);
    Route::resource('activites', ActiviteController::class);
    Route::resource('alertes', AlerteController::class);

    // Export/Import
    Route::get('/animaux/export/csv', [AnimalController::class, 'exportCSV'])->name('animaux.export.csv');
    Route::post('/animaux/import/csv', [AnimalController::class, 'importCSV'])->name('animaux.import.csv');

    // Logs
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [LogController::class, 'show'])->name('logs.show');

    // API pour mobile
    Route::prefix('api')->group(function () {
        Route::get('/animaux', function() {
            return App\Models\Animal::with('employe')->get();
        });
        Route::get('/stocks', function() {
            return App\Models\Stock::all();
        });
        Route::get('/alertes', function() {
            return App\Models\Alerte::critiques()->nonResolues()->get();
        });
        Route::post('/alertes', [AlerteController::class, 'store']);
    });
});

require __DIR__.'/auth.php'; 