<?php

use App\Http\Controllers\Api\OwnerTrustedUserController;
use App\Http\Controllers\Api\TelegramAuthController;
use App\Http\Middleware\EnsureIsOwner;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public – Telegram Web App login
    Route::post('/auth/telegram-webapp', [TelegramAuthController::class, 'login']);

    // Protected – requires Bearer token (User)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/users/complete-profile', [TelegramAuthController::class, 'completeProfile']);
        Route::get('/user/profile', [TelegramAuthController::class, 'profile']);
    });

    // Protected – Owner only
    Route::prefix('owner')
        ->middleware(['auth:sanctum', EnsureIsOwner::class])
        ->group(function () {
            Route::post('/users/{id}/mark-trusted',   [OwnerTrustedUserController::class, 'markTrusted']);
            Route::post('/users/{id}/remove-trusted', [OwnerTrustedUserController::class, 'removeTrusted']);
        });
});
