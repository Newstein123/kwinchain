<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CompleteProfileRequest;
use App\Http\Requests\Api\TelegramWebAppLoginRequest;
use App\Http\Resources\Api\UserProfileResource;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Services\TelegramWebAppAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramAuthController extends Controller
{
    public function __construct(
        private TelegramWebAppAuthService $telegramAuthService,
    ) {}

    /**
     * POST /api/v1/auth/telegram-webapp
     *
     * Validate Telegram init_data, find-or-create user, issue Sanctum token.
     */
    public function login(TelegramWebAppLoginRequest $request): JsonResponse
    {
        $initData = $request->validated('init_data');

        if (! $this->telegramAuthService->validateInitData($initData)) {
            return response()->json([
                'message' => 'Invalid Telegram authentication data',
            ], 401);
        }

        $telegramUser = $this->telegramAuthService->extractUserData($initData);

        if (empty($telegramUser['telegram_user_id'])) {
            return response()->json([
                'message' => 'Invalid user data in Telegram payload',
            ], 422);
        }

        $fullName = trim(($telegramUser['first_name'] ?? '') . ' ' . ($telegramUser['last_name'] ?? ''));

        $user = User::updateOrCreate(
            ['telegram_user_id' => $telegramUser['telegram_user_id']],
            [
                'name' => $fullName ?: 'Telegram User',
                'telegram_username' => $telegramUser['username'],
            ]
        );

        $user->update(['last_login_at' => now()]);

        // Revoke previous tokens and issue a new one
        $user->tokens()->delete();
        $token = $user->createToken('telegram-webapp')->plainTextToken;

        return response()->json([
            'message' => 'Login Successfully',
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * POST /api/v1/users/complete-profile
     *
     * Update email and phone, mark profile as completed.
     */
    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'profile_completed' => true,
        ]);

        return response()->json([
            'message' => 'Profile completed successfully',
            'data' => null,
        ]);
    }

    /**
     * GET /api/v1/user/profile
     *
     * Return authenticated user's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Profile Data retrieved successfully',
            'data' => [
                'user' => new UserProfileResource($user),
            ],
        ]);
    }
}
