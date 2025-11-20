<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::post('/transactions/get', [App\Http\Controllers\HomeController::class, 'getTransactions'])->name('transactions.get');
    Route::post('/transactions/available-months', [App\Http\Controllers\HomeController::class, 'getAvailableMonths'])->name('transactions.available-months');
    Route::patch('/transactions/{transaction}', [App\Http\Controllers\HomeController::class, 'updateTransaction'])->name('transactions.update');
    Route::post('/transactions/preview', [App\Http\Controllers\ImportController::class, 'preview'])->name('transactions.preview');
    Route::post('/transactions/import', [App\Http\Controllers\ImportController::class, 'import'])->name('transactions.import');
    Route::get('/transactions/export/excel', [App\Http\Controllers\ExportController::class, 'exportExcel'])->name('transactions.export.excel');
    Route::get('/transactions/export/csv', [App\Http\Controllers\ExportController::class, 'exportCsv'])->name('transactions.export.csv');
    Route::get('/transactions/export/pdf', [App\Http\Controllers\ExportController::class, 'exportPdf'])->name('transactions.export.pdf');
    
    // Category management routes
    Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{category}', [App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/categories/all', [App\Http\Controllers\CategoryController::class, 'getAll'])->name('categories.all');
    
    // Bank management routes
    Route::get('/banks', [App\Http\Controllers\BankController::class, 'index'])->name('banks.index');
    Route::post('/banks', [App\Http\Controllers\BankController::class, 'store'])->name('banks.store');
    Route::delete('/banks/{bank}', [App\Http\Controllers\BankController::class, 'destroy'])->name('banks.destroy');
    
    // Rule management routes
    Route::get('/rules', [App\Http\Controllers\RuleController::class, 'index'])->name('rules.index');
    Route::post('/rules', [App\Http\Controllers\RuleController::class, 'store'])->name('rules.store');
    Route::put('/rules/{rule}', [App\Http\Controllers\RuleController::class, 'update'])->name('rules.update');
    Route::delete('/rules/{rule}', [App\Http\Controllers\RuleController::class, 'destroy'])->name('rules.destroy');
    Route::post('/rules/apply', [App\Http\Controllers\RuleController::class, 'applyRules'])->name('rules.apply');
});
