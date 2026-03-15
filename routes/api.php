<?php

use App\Http\Controllers\Api\OwnerFieldController;
use App\Http\Controllers\Api\TelegramAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public – Telegram Web App login
    Route::post('/auth/telegram-webapp', [TelegramAuthController::class, 'login']);

    // Protected – requires Bearer token
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/users/complete-profile', [TelegramAuthController::class, 'completeProfile']);
        Route::get('/user/profile', [TelegramAuthController::class, 'profile']);

        // Owner – Fields CRUD
        Route::prefix('owner')->group(function () {
            Route::apiResource('fields', OwnerFieldController::class);
            Route::patch('fields/{field}/status', [OwnerFieldController::class, 'changeStatus']);
        });
    });
});
