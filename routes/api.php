<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;

/*
|--------------------------------------------------------------------------
| WarKom API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Authentication Endpoints
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
        Route::post('register', [AuthController::class, 'register'])->name('api.auth.register');

        Route::middleware(['auth:sanctum,web'])->group(function () {
            Route::get('me', [AuthController::class, 'me'])->name('api.auth.me');
            Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        });
    });

    // Public Payment Webhook / Callback Notification Endpoint
    Route::post('transactions/webhook', [TransactionController::class, 'webhook'])->name('api.transactions.webhook');

    // Authenticated Transaction Endpoints (Supports both Token & Web Session auth)
    Route::middleware(['auth:sanctum,web'])->group(function () {
        Route::get('transactions', [TransactionController::class, 'index'])->name('api.transactions.index');
        Route::post('transactions', [TransactionController::class, 'store'])->name('api.transactions.store');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('api.transactions.show');
        Route::get('transactions/invoice/{invoice_number}', [TransactionController::class, 'show'])->name('api.transactions.show.invoice');
        Route::post('transactions/{transaction}/pay', [TransactionController::class, 'pay'])->name('api.transactions.pay');
        Route::post('transactions/{transaction}/complete', [TransactionController::class, 'complete'])->name('api.transactions.complete');
        Route::post('transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('api.transactions.cancel');
        Route::post('transactions/{transaction}/review', [TransactionController::class, 'review'])->name('api.transactions.review');
    });

});
