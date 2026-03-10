<?php

use App\Models\User;
use App\Services\TelegramWebAppAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helper: build a valid Telegram init_data string with a correct HMAC hash
|--------------------------------------------------------------------------
*/
function buildInitData(array $user, string $botToken): string
{
    $userJson = json_encode($user);

    $fields = [
        'query_id' => 'AAHdF6IQAAAAAN0XohDhrOrc',
        'user' => $userJson,
        'auth_date' => (string) time(),
    ];

    // Sort alphabetically by key
    ksort($fields);

    $dataCheckString = collect($fields)
        ->map(fn ($v, $k) => "{$k}={$v}")
        ->implode("\n");

    $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
    $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

    $fields['hash'] = $hash;

    return http_build_query($fields);
}

/*
|--------------------------------------------------------------------------
| POST /api/v1/auth/telegram-webapp
|--------------------------------------------------------------------------
*/
describe('Telegram Web App Login', function () {

    it('creates a new user and returns a token on valid init_data', function () {
        config(['services.telegram.bot_token' => 'test-bot-token-123']);

        $initData = buildInitData([
            'id' => 6358273629,
            'first_name' => 'Min',
            'username' => 'astrodev',
        ], 'test-bot-token-123');

        $response = $this->postJson('/api/v1/auth/telegram-webapp', [
            'init_data' => $initData,
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Login Successfully',
            ])
            ->assertJsonStructure([
                'data' => ['token', 'user' => ['id', 'name', 'telegram_user_id']],
            ]);

        $this->assertDatabaseHas('users', [
            'telegram_user_id' => 6358273629,
            'name' => 'Min',
            'telegram_username' => 'astrodev',
        ]);
    });

    it('returns existing user without creating a duplicate', function () {
        config(['services.telegram.bot_token' => 'test-bot-token-123']);

        User::create([
            'telegram_user_id' => 6358273629,
            'name' => 'OldName',
            'telegram_username' => 'old_username',
        ]);

        $initData = buildInitData([
            'id' => 6358273629,
            'first_name' => 'Min',
            'username' => 'astrodev',
        ], 'test-bot-token-123');

        $response = $this->postJson('/api/v1/auth/telegram-webapp', [
            'init_data' => $initData,
        ]);

        $response->assertOk();
        expect(User::where('telegram_user_id', 6358273629)->count())->toBe(1);
        // Name should be updated
        expect(User::where('telegram_user_id', 6358273629)->first()->name)->toBe('Min');
    });

    it('rejects login with invalid hash', function () {
        config(['services.telegram.bot_token' => 'test-bot-token-123']);

        $initData = 'query_id=AAHdF6IQAAAAAN0XohDhrOrc&user='.urlencode('{"id":6358273629,"first_name":"Min","username":"astrodev"}').'&auth_date=1739212312&hash=invalidhash';

        $response = $this->postJson('/api/v1/auth/telegram-webapp', [
            'init_data' => $initData,
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid Telegram authentication data']);
    });

    it('rejects login without init_data', function () {
        $response = $this->postJson('/api/v1/auth/telegram-webapp', []);

        $response->assertStatus(422);
    });
});

/*
|--------------------------------------------------------------------------
| POST /api/v1/users/complete-profile
|--------------------------------------------------------------------------
*/
describe('Complete Profile', function () {

    it('updates email and phone and marks profile as completed', function () {
        $user = User::create([
            'telegram_user_id' => 111222333,
            'name' => 'TestUser',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/users/complete-profile', [
                'email' => 'admin@gmail.com',
                'phone' => '09123456789',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Profile completed successfully',
                'data' => null,
            ]);

        $user->refresh();
        expect($user->email)->toBe('admin@gmail.com');
        expect($user->phone)->toBe('09123456789');
        expect($user->profile_completed)->toBeTrue();
    });

    it('returns 401 for unauthenticated request', function () {
        $response = $this->postJson('/api/v1/users/complete-profile', [
            'email' => 'admin@gmail.com',
            'phone' => '09123456789',
        ]);

        $response->assertStatus(401);
    });

    it('validates email and phone are required', function () {
        $user = User::create([
            'telegram_user_id' => 111222333,
            'name' => 'TestUser',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/users/complete-profile', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'phone']);
    });
});

/*
|--------------------------------------------------------------------------
| GET /api/v1/user/profile
|--------------------------------------------------------------------------
*/
describe('Get Profile', function () {

    it('returns user profile data', function () {
        $user = User::create([
            'telegram_user_id' => 987654321,
            'name' => 'ProfileUser',
            'email' => 'user@example.com',
            'phone' => '09111222333',
            'profile_completed' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user/profile');

        $response->assertOk()
            ->assertJson([
                'message' => 'Profile Data retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => 'ProfileUser',
                        'email' => 'user@example.com',
                        'phone' => '09111222333',
                        'is_profile_completed' => true,
                    ],
                ],
            ]);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = $this->getJson('/api/v1/user/profile');

        $response->assertStatus(401);
    });
});
