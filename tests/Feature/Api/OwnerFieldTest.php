<?php

use App\Enums\FieldSlotDuration;
use App\Enums\FieldStatus;
use App\Enums\FieldSurfaceType;
use App\Models\Field;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helper: create an Owner and a User whose email matches the Owner
|--------------------------------------------------------------------------
*/
function makeOwnerWithUser(): array
{
    $owner = Owner::create([
        'name'     => 'Test Owner',
        'email'    => 'owner@example.com',
        'phone'    => '09111111111',
        'password' => bcrypt('password'),
    ]);

    $user = User::create([
        'telegram_user_id' => 999888777,
        'name'             => 'Test Owner User',
        'email'            => 'owner@example.com',
    ]);

    return [$owner, $user];
}

/*
|--------------------------------------------------------------------------
| POST /api/v1/owner/fields
|--------------------------------------------------------------------------
*/
describe('Create Field', function () {

    it('creates a field with valid data', function () {
        [, $user] = makeOwnerWithUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/owner/fields', [
                'name'           => 'Power Futsal',
                'location'       => 'Yangon',
                'price_per_hour' => 30000,
                'deposit_amount' => 20000,
                'description'    => '5v5 turf field',
            ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Field created successfully.'])
            ->assertJsonStructure([
                'data' => ['id', 'name', 'location', 'price_per_hour', 'deposit_amount', 'status', 'image_url'],
            ]);

        $this->assertDatabaseHas('fields', [
            'name'     => 'Power Futsal',
            'location' => 'Yangon',
        ]);
    });

    it('creates a field with an image upload', function () {
        Storage::fake('public');
        [, $user] = makeOwnerWithUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/owner/fields', [
                'name'           => 'Green Futsal',
                'location'       => 'Mandalay',
                'price_per_hour' => 25000,
                'deposit_amount' => 15000,
                'image'          => UploadedFile::fake()->image('field.jpg', 800, 600),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseCount('field_images', 1);
    });

    it('rejects creation with missing required fields', function () {
        [, $user] = makeOwnerWithUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/owner/fields', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'location', 'price_per_hour', 'deposit_amount']);
    });

    it('rejects creation with name shorter than 3 characters', function () {
        [, $user] = makeOwnerWithUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/owner/fields', [
                'name'           => 'AB',
                'location'       => 'Yangon',
                'price_per_hour' => 10000,
                'deposit_amount' => 5000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = $this->postJson('/api/v1/owner/fields', [
            'name'           => 'Power Futsal',
            'location'       => 'Yangon',
            'price_per_hour' => 30000,
            'deposit_amount' => 20000,
        ]);

        $response->assertStatus(401);
    });
});

/*
|--------------------------------------------------------------------------
| GET /api/v1/owner/fields
|--------------------------------------------------------------------------
*/
describe('List Fields', function () {

    it('returns only the authenticated owner\'s fields', function () {
        [$owner, $user] = makeOwnerWithUser();

        Field::create([
            'owner_id'       => $owner->id,
            'name'           => 'My Field 1',
            'location'       => 'Yangon',
            'price_per_hour' => 20000,
            'deposit_amount' => 10000,
            'surface_type'   => FieldSurfaceType::Futsal,
            'slot_duration'  => FieldSlotDuration::Minutes60,
            'status'         => FieldStatus::Active,
        ]);

        Field::create([
            'owner_id'       => $owner->id,
            'name'           => 'My Field 2',
            'location'       => 'Bago',
            'price_per_hour' => 15000,
            'deposit_amount' => 5000,
            'surface_type'   => FieldSurfaceType::Futsal,
            'slot_duration'  => FieldSlotDuration::Minutes60,
            'status'         => FieldStatus::Active,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/owner/fields');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('returns 401 for unauthenticated request', function () {
        $this->getJson('/api/v1/owner/fields')->assertStatus(401);
    });
});

/*
|--------------------------------------------------------------------------
| GET /api/v1/owner/fields/{field}
|--------------------------------------------------------------------------
*/
describe('Show Field', function () {

    it('returns a single field owned by the owner', function () {
        [$owner, $user] = makeOwnerWithUser();

        $field = Field::create([
            'owner_id'       => $owner->id,
            'name'           => 'Sunrise Futsal',
            'location'       => 'Yangon',
            'price_per_hour' => 18000,
            'deposit_amount' => 9000,
            'surface_type'   => FieldSurfaceType::Futsal,
            'slot_duration'  => FieldSlotDuration::Minutes60,
            'status'         => FieldStatus::Active,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/owner/fields/{$field->id}");

        $response->assertOk()
            ->assertJson(['data' => ['name' => 'Sunrise Futsal']]);
    });

    it('returns 404 for a field not owned by the owner', function () {
        [, $user] = makeOwnerWithUser();

        $otherOwner = Owner::create([
            'name'     => 'Other Owner',
            'email'    => 'other@example.com',
            'phone'    => '09222222222',
            'password' => bcrypt('password'),
        ]);

        $field = Field::create([
            'owner_id'       => $otherOwner->id,
            'name'           => 'Other Field',
            'location'       => 'Mandalay',
            'price_per_hour' => 20000,
            'deposit_amount' => 10000,
            'surface_type'   => FieldSurfaceType::Futsal,
            'slot_duration'  => FieldSlotDuration::Minutes60,
            'status'         => FieldStatus::Active,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/owner/fields/{$field->id}");

        $response->assertStatus(404);
    });
});

/*
|--------------------------------------------------------------------------
| PUT /api/v1/owner/fields/{field}
|--------------------------------------------------------------------------
*/
describe('Update Field', function () {

    it('updates a field with valid data', function () {
        [$owner, $user] = makeOwnerWithUser();

        $field = Field::create([
            'owner_id'       => $owner->id,
            'name'           => 'Old Name',
            'location'       => 'Yangon',
            'price_per_hour' => 10000,
            'deposit_amount' => 5000,
            'surface_type'   => FieldSurfaceType::Futsal,
            'slot_duration'  => FieldSlotDuration::Minutes60,
            'status'         => FieldStatus::Active,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/owner/fields/{$field->id}", [
                'name'           => 'New Name Futsal',
                'price_per_hour' => 35000,
            ]);

        $response->assertOk()
            ->assertJson(['message' => 'Field updated successfully.']);

        $this->assertDatabaseHas('fields', [
            'id'             => $field->id,
            'name'           => 'New Name Futsal',
            'price_per_hour' => 35000,
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| DELETE /api/v1/owner/fields/{field}
|--------------------------------------------------------------------------
*/
describe('Delete Field', function () {

    it('deletes a field with no bookings', function () {
        [$owner, $user] = makeOwnerWithUser();

        $field = Field::create([
            'owner_id'       => $owner->id,
            'name'           => 'To Delete',
            'location'       => 'Yangon',
            'price_per_hour' => 10000,
            'deposit_amount' => 5000,
            'surface_type'   => FieldSurfaceType::Futsal,
            'slot_duration'  => FieldSlotDuration::Minutes60,
            'status'         => FieldStatus::Active,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/owner/fields/{$field->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Field deleted successfully.']);

        $this->assertDatabaseMissing('fields', ['id' => $field->id]);
    });
});

/*
|--------------------------------------------------------------------------
| PATCH /api/v1/owner/fields/{field}/status
|--------------------------------------------------------------------------
*/
describe('Change Field Status', function () {

    it('sets field status to inactive', function () {
        [$owner, $user] = makeOwnerWithUser();

        $field = Field::create([
            'owner_id'       => $owner->id,
            'name'           => 'Active Field',
            'location'       => 'Yangon',
            'price_per_hour' => 10000,
            'deposit_amount' => 5000,
            'surface_type'   => FieldSurfaceType::Futsal,
            'slot_duration'  => FieldSlotDuration::Minutes60,
            'status'         => FieldStatus::Active,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/owner/fields/{$field->id}/status", ['status' => 0]);

        $response->assertOk()
            ->assertJson(['message' => 'Field status updated successfully.']);

        $this->assertDatabaseHas('fields', [
            'id'     => $field->id,
            'status' => FieldStatus::Inactive->value,
        ]);
    });

    it('sets field status to active', function () {
        [$owner, $user] = makeOwnerWithUser();

        $field = Field::create([
            'owner_id'       => $owner->id,
            'name'           => 'Inactive Field',
            'location'       => 'Yangon',
            'price_per_hour' => 10000,
            'deposit_amount' => 5000,
            'surface_type'   => FieldSurfaceType::Futsal,
            'slot_duration'  => FieldSlotDuration::Minutes60,
            'status'         => FieldStatus::Inactive,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/owner/fields/{$field->id}/status", ['status' => 1]);

        $response->assertOk();

        $this->assertDatabaseHas('fields', [
            'id'     => $field->id,
            'status' => FieldStatus::Active->value,
        ]);
    });
});
