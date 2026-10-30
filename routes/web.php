<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AnalysisResultController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// 1. Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// 2. Datasets
Route::get('/datasets/template', [DatasetController::class, 'downloadTemplate'])->name('datasets.template');
Route::post('/datasets/preview', [DatasetController::class, 'preview'])->name('datasets.preview');
Route::resource('datasets', DatasetController::class)->except(['edit', 'update']);

// 3. Transactions
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

// 4. Apriori Analyses & Results
Route::resource('analyses', AnalysisController::class)->except(['edit', 'update', 'show']);
Route::get('/analyses/{analysis}', [AnalysisResultController::class, 'show'])->name('analyses.show');

// 5. Reports & Exports
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/analyses/{analysis}/export/pdf', [ReportController::class, 'exportPdf'])->name('analyses.export.pdf');
Route::get('/analyses/{analysis}/export/excel', [ReportController::class, 'exportExcel'])->name('analyses.export.excel');
Route::get('/analyses/{analysis}/print', [ReportController::class, 'print'])->name('analyses.print');

// 6. Settings
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile.update');
