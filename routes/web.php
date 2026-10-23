<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)->name('dashboard');

// Jika akses root '/', arahkan berdasarkan status login atau ke halaman info
Route::get('/', function () {
    return \Illuminate\Support\Facades\Auth::check()
        ? redirect()->route('pos.create')
        : redirect()->route('info');
});

Route::middleware('guest')->group(function () {
    // Halaman informasi khusus tamu
    Route::get('/info', [App\Http\Controllers\InfoController::class, 'index'])
        ->name('info');

    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    // Kasir POS
    Route::get('/pos', [TransactionController::class, 'create'])
        ->name('pos.create');

    Route::post('/pos', [TransactionController::class, 'store'])
        ->name('transactions.store');

    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');

    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
        ->name('transactions.show');

    // Khusus admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)
            ->except('show');

        Route::resource('products', ProductController::class)
            ->except(['show', 'destroy']);

        // Laporan penjualan
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        // Export laporan ke CSV
        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])
            ->name('reports.export.csv');

        // Export laporan ke Excel
        Route::get('/reports/export/xlsx', [ReportController::class, 'exportExcel'])
            ->name('reports.export.xlsx');

        // Export laporan ke PDF
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])
            ->name('reports.export.pdf');
    });
});
