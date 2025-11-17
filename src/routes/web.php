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
    Route::patch('/transactions/{transaction}', [App\Http\Controllers\HomeController::class, 'updateTransaction'])->name('transactions.update');
});
