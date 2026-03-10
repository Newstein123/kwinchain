<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class TelegramWebAppAuthService
{
    /**
     * Validate Telegram Web App init_data using HMAC-SHA256.
     */
    public function validateInitData(string $initData): bool
    {
        $botToken = Config::get('services.telegram.bot_token');

        if (empty($botToken)) {
            return false;
        }

        // Strip any trailing invisible/whitespace characters (e.g. from terminal paste)
        $initData = trim($initData);

        // Parse the init_data string into key-value pairs
        parse_str($initData, $parsed);

        if (! isset($parsed['hash'])) {
            return false;
        }

        $receivedHash = trim($parsed['hash']);
        unset($parsed['hash']);

        // Sort remaining fields alphabetically by key
        ksort($parsed);

        // Build data_check_string: each field as "key=value", joined by "\n"
        $dataCheckString = collect($parsed)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode("\n");

        // Compute secret_key = HMAC-SHA256("WebAppData", bot_token)
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);

        // Compute hash = HMAC-SHA256(secret_key, data_check_string)
        $computedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($computedHash, $receivedHash);
    }

    /**
     * Extract user data from Telegram init_data payload.
     */
    public function extractUserData(string $initData): array
    {
        parse_str($initData, $parsed);

        $userData = json_decode($parsed['user'] ?? '{}', true);

        return [
            'telegram_user_id' => $userData['id'] ?? null,
            'first_name' => $userData['first_name'] ?? null,
            'last_name' => $userData['last_name'] ?? null,
            'username' => $userData['username'] ?? null,
        ];
    }
}
