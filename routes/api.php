<?php

use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('api.transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('api.transactions.store');
});
