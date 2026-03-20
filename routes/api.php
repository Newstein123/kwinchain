<?php

use App\Http\Controllers\Api\OwnerTrustedUserController;
use App\Http\Controllers\Api\Owner\OwnerFieldScheduleController;
use App\Http\Controllers\Api\Owner\OwnerFieldTimeSlotController;
use App\Http\Controllers\Api\Owner\OwnerRegistrationController;
use App\Http\Controllers\Api\TelegramAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public – Telegram Web App login
    Route::post('/auth/telegram-webapp', [TelegramAuthController::class, 'login']);

    // Protected – requires Bearer token (User)
    // Owner registration & KYC (public)
    Route::post('/owner/register', [OwnerRegistrationController::class, 'register']);
    Route::post('/owner/verify-otp', [OwnerRegistrationController::class, 'verifyOtp']);

    Route::middleware('auth:owner')->group(function () {
        Route::prefix('owner')->group(function () {
            // Trusted Users
            Route::post('/users/{id}/mark-trusted',   [OwnerTrustedUserController::class, 'markTrusted']);
            Route::post('/users/{id}/remove-trusted', [OwnerTrustedUserController::class, 'removeTrusted']);

            // Owner registration & KYC (protected – owner token)
            Route::post('/verify-business', [OwnerRegistrationController::class, 'verifyBusiness']);
            Route::post('/verify-identity', [OwnerRegistrationController::class, 'verifyIdentity']);
            Route::post('/payout-account', [OwnerRegistrationController::class, 'payoutAccount']);

            // Weekly schedules
            Route::post('/fields/{id}/schedules', [OwnerFieldScheduleController::class, 'add']);
            Route::put('/schedules/{id}', [OwnerFieldScheduleController::class, 'update']);
            Route::get('/fields/{id}/schedules', [OwnerFieldScheduleController::class, 'list']);

            // Time slots
            Route::post('/fields/{id}/generate-slots', [OwnerFieldTimeSlotController::class, 'generate']);
            Route::post('/fields/{id}/manual-slots', [OwnerFieldTimeSlotController::class, 'manual']);
            Route::get('/fields/{id}/slots', [OwnerFieldTimeSlotController::class, 'listByDate']);
            Route::post('/slots/{id}/block', [OwnerFieldTimeSlotController::class, 'block']);
            Route::post('/slots/{id}/unblock', [OwnerFieldTimeSlotController::class, 'unblock']);
            Route::delete('/slots/{id}', [OwnerFieldTimeSlotController::class, 'delete']);
            Route::post('/fields/{id}/bulk-block', [OwnerFieldTimeSlotController::class, 'bulkBlock']);
        });
    });

    // Protected – requires Bearer token (user)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/users/complete-profile', [TelegramAuthController::class, 'completeProfile']);
        Route::get('/user/profile', [TelegramAuthController::class, 'profile']);
    });
});
