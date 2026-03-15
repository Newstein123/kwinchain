<?php

use App\Http\Controllers\Api\OwnerFieldController;
use App\Http\Controllers\Api\Owner\OwnerRegistrationController;
use App\Http\Controllers\Api\OwnerFieldController;
use App\Http\Controllers\Api\TelegramAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public – Telegram Web App login
    Route::post('/auth/telegram-webapp', [TelegramAuthController::class, 'login']);

    // Owner registration & KYC (public)
    Route::post('/owners/register', [OwnerRegistrationController::class, 'register']);
    Route::post('/owners/verify-otp', [OwnerRegistrationController::class, 'verifyOtp']);

    // Owner registration & KYC (protected – owner token)
    Route::middleware('auth:owner')->group(function () {
        Route::post('/owners/verify-identity', [OwnerRegistrationController::class, 'verifyIdentity']);
        Route::post('/owners/verify-business', [OwnerRegistrationController::class, 'verifyBusiness']);
        Route::post('/owners/payout-account', [OwnerRegistrationController::class, 'payoutAccount']);
    });

    // Protected – requires Bearer token (user)
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
