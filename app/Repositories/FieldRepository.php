<?php

namespace App\Repositories;

use App\Enums\FieldSlotDuration;
use App\Enums\FieldStatus;
use App\Enums\FieldSurfaceType;
use App\Models\Field;
use App\Models\FieldImage;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Collection;

class FieldRepository
{
    /**
     * Get all fields for a given owner, newest first.
     *
     * @return Collection<int, Field>
     */
    public function allForOwner(Owner $owner): Collection
    {
        return Field::with('images')
            ->where('owner_id', $owner->id)
            ->latest()
            ->get();
    }

    /**
     * Find a field by ID, eager-loading images.
     */
    public function findWithImages(int $id): ?Field
    {
        return Field::with('images')->find($id);
    }

    /**
     * Create a new field for the given owner.
     */
    public function create(Owner $owner, array $data): Field
    {
        return Field::create([
            'owner_id'       => $owner->id,
            'name'           => $data['name'],
            'location'       => $data['location'],
            'price_per_hour' => $data['price_per_hour'],
            'deposit_amount' => $data['deposit_amount'],
            'description'    => $data['description'] ?? null,
            'surface_type'   => FieldSurfaceType::Futsal,
            'slot_duration'  => FieldSlotDuration::Minutes60,
            'status'         => FieldStatus::Active,
        ]);
    }

    /**
     * Update an existing field's attributes.
     */
    public function update(Field $field, array $data): Field
    {
        $updateData = array_filter([
            'name'           => $data['name'] ?? null,
            'location'       => $data['location'] ?? null,
            'price_per_hour' => $data['price_per_hour'] ?? null,
            'deposit_amount' => $data['deposit_amount'] ?? null,
        ], fn ($v) => $v !== null);

        // description may be explicitly set to null
        if (array_key_exists('description', $data)) {
            $updateData['description'] = $data['description'];
        }

        $field->update($updateData);

        return $field;
    }

    /**
     * Delete a field record from the database.
     */
    public function delete(Field $field): void
    {
        $field->delete();
    }

    /**
     * Update the field's status.
     */
    public function updateStatus(Field $field, FieldStatus $status): Field
    {
        $field->update(['status' => $status]);

        return $field;
    }

    /**
     * Add an image record linked to the field.
     */
    public function addImage(Field $field, string $storedPath, bool $isPrimary): FieldImage
    {
        return $field->images()->create([
            'image_url'  => $storedPath,
            'is_primary' => $isPrimary,
        ]);
    }

    /**
     * Delete the primary image record for the field and return the stored path
     * so the caller can remove the file from storage.
     */
    public function deletePrimaryImage(Field $field): ?string
    {
        $image = $field->images()->where('is_primary', true)->first();

        if (! $image) {
            return null;
        }

        $path = $image->image_url;
        $image->delete();

        return $path;
    }

    /**
     * Return the stored paths of all images for the field and delete their records.
     *
     * @return list<string>
     */
    public function deleteAllImages(Field $field): array
    {
        $paths = $field->images->pluck('image_url')->all();
        $field->images()->delete();

        return $paths;
    }
}
