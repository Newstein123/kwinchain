<?php

use App\Http\Controllers\Api\OwnerTrustedUserController;
use App\Http\Controllers\Api\Owner\OwnerRegistrationController;
use App\Http\Controllers\Api\TelegramAuthController;
use App\Http\Middleware\EnsureIsOwner;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public – Telegram Web App login
    Route::post('/auth/telegram-webapp', [TelegramAuthController::class, 'login']);

    // Protected – requires Bearer token (User)
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
    });

    // Protected – Owner only
    Route::prefix('owner')
        ->middleware(['auth:sanctum', EnsureIsOwner::class])
        ->group(function () {
            Route::post('/users/{id}/mark-trusted',   [OwnerTrustedUserController::class, 'markTrusted']);
            Route::post('/users/{id}/remove-trusted', [OwnerTrustedUserController::class, 'removeTrusted']);
        });
});
