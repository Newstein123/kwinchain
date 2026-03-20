<?php

use App\Enums\OwnerTrustedUserLevel;
use App\Models\Owner;
use App\Models\OwnerTrustedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Create an Owner and issue it a Sanctum token, returning [$owner, $token].
 *
 * @return array{0: Owner, 1: string}
 */
function createOwnerWithToken(): array
{
    $owner = Owner::create([
        'name'     => 'Test Owner',
        'email'    => 'owner@test.com',
        'phone'    => '09000000000',
        'password' => bcrypt('secret'),
    ]);

    $token = $owner->createToken('owner-api')->plainTextToken;

    return [$owner, $token];
}

// ---------------------------------------------------------------------------
// POST /api/v1/owner/users/{id}/mark-trusted
// ---------------------------------------------------------------------------

describe('Mark User as Trusted', function () {

    it('owner can mark a user as trusted', function () {
        [$owner, $token] = createOwnerWithToken();

        $user = User::create([
            'telegram_user_id' => 111111111,
            'name'             => 'Alice',
        ]);

        $response = $this
            ->withToken($token)
            ->postJson("/api/v1/owner/users/{$user->id}/mark-trusted");

        $response->assertOk()
            ->assertJson([
                'message' => 'User marked as trusted.',
                'data'    => ['trusted' => true],
            ]);

        $this->assertDatabaseHas('owner_trusted_users', [
            'owner_id'    => $owner->id,
            'user_id'     => $user->id,
            'trust_level' => OwnerTrustedUserLevel::Trusted->value,
        ]);
    });

    it('marking an already-trusted user is idempotent (no duplicate rows)', function () {
        [$owner, $token] = createOwnerWithToken();

        $user = User::create([
            'telegram_user_id' => 222222222,
            'name'             => 'Bob',
        ]);

        // Mark twice
        $this->withToken($token)->postJson("/api/v1/owner/users/{$user->id}/mark-trusted");
        $this->withToken($token)->postJson("/api/v1/owner/users/{$user->id}/mark-trusted");

        expect(
            OwnerTrustedUser::where(['owner_id' => $owner->id, 'user_id' => $user->id])->count()
        )->toBe(1);
    });

    it('returns 404 when user does not exist', function () {
        [, $token] = createOwnerWithToken();

        $this->withToken($token)
            ->postJson('/api/v1/owner/users/99999/mark-trusted')
            ->assertNotFound()
            ->assertJson(['message' => 'User not found.']);
    });

    it('returns 401 for unauthenticated request', function () {
        $user = User::create(['telegram_user_id' => 333333333, 'name' => 'Charlie']);

        $this->postJson("/api/v1/owner/users/{$user->id}/mark-trusted")
            ->assertUnauthorized();
    });

    it('returns 403 when a regular user tries to access the endpoint', function () {
        $regularUser = User::create([
            'telegram_user_id' => 444444444,
            'name'             => 'Dave',
        ]);

        $targetUser = User::create([
            'telegram_user_id' => 555555555,
            'name'             => 'Eve',
        ]);

        $this->actingAs($regularUser, 'sanctum')
            ->postJson("/api/v1/owner/users/{$targetUser->id}/mark-trusted")
            ->assertForbidden();
    });

});

// ---------------------------------------------------------------------------
// POST /api/v1/owner/users/{id}/remove-trusted
// ---------------------------------------------------------------------------

describe('Remove User Trust', function () {

    it('owner can remove trusted status from a user', function () {
        [$owner, $token] = createOwnerWithToken();

        $user = User::create([
            'telegram_user_id' => 666666666,
            'name'             => 'Frank',
        ]);

        // Seed a trusted record first
        OwnerTrustedUser::create([
            'owner_id'    => $owner->id,
            'user_id'     => $user->id,
            'trust_level' => OwnerTrustedUserLevel::Trusted,
        ]);

        $response = $this
            ->withToken($token)
            ->postJson("/api/v1/owner/users/{$user->id}/remove-trusted");

        $response->assertOk()
            ->assertJson([
                'message' => 'User trust removed.',
                'data'    => ['trusted' => false],
            ]);

        $this->assertDatabaseMissing('owner_trusted_users', [
            'owner_id' => $owner->id,
            'user_id'  => $user->id,
        ]);
    });

    it('removing trust on a non-trusted user returns 200 gracefully', function () {
        [, $token] = createOwnerWithToken();

        $user = User::create([
            'telegram_user_id' => 777777777,
            'name'             => 'Grace',
        ]);

        // No record seeded – should still succeed (idempotent delete)
        $this->withToken($token)
            ->postJson("/api/v1/owner/users/{$user->id}/remove-trusted")
            ->assertOk()
            ->assertJson(['data' => ['trusted' => false]]);
    });

    it('returns 404 when user does not exist', function () {
        [, $token] = createOwnerWithToken();

        $this->withToken($token)
            ->postJson('/api/v1/owner/users/99999/remove-trusted')
            ->assertNotFound()
            ->assertJson(['message' => 'User not found.']);
    });

    it('returns 401 for unauthenticated request', function () {
        $user = User::create(['telegram_user_id' => 888888888, 'name' => 'Hank']);

        $this->postJson("/api/v1/owner/users/{$user->id}/remove-trusted")
            ->assertUnauthorized();
    });

    it('returns 403 when a regular user tries to access the endpoint', function () {
        $regularUser = User::create([
            'telegram_user_id' => 901010101,
            'name'             => 'Ivy',
        ]);

        $targetUser = User::create([
            'telegram_user_id' => 902020202,
            'name'             => 'Jack',
        ]);

        $this->actingAs($regularUser, 'sanctum')
            ->postJson("/api/v1/owner/users/{$targetUser->id}/remove-trusted")
            ->assertForbidden();
    });

});
