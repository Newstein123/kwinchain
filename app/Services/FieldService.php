<?php

namespace App\Services;

use App\Enums\FieldStatus;
use App\Models\Field;
use App\Models\Owner;
use App\Repositories\FieldRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FieldService
{
    public function __construct(
        private readonly FieldRepository $fieldRepository,
    ) {}

    /**
     * Return all fields belonging to the given owner.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Field>
     */
    public function listForOwner(Owner $owner)
    {
        return $this->fieldRepository->allForOwner($owner);
    }

    /**
     * Create a new field for the owner, optionally uploading an image.
     */
    public function create(Owner $owner, array $data, ?UploadedFile $image = null): Field
    {
        $field = $this->fieldRepository->create($owner, $data);

        if ($image) {
            $path = $image->store('fields', 'public');
            $this->fieldRepository->addImage($field, $path, true);
        }

        return $field->load('images');
    }

    /**
     * Update an existing field's attributes, optionally replacing the primary image.
     */
    public function update(Field $field, array $data, ?UploadedFile $image = null): Field
    {
        $this->fieldRepository->update($field, $data);

        if ($image) {
            // Remove old primary image file from storage
            $oldPath = $this->fieldRepository->deletePrimaryImage($field);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $image->store('fields', 'public');
            $this->fieldRepository->addImage($field, $path, true);
        }

        return $field->load('images');
    }

    /**
     * Delete a field and all its associated images from storage.
     */
    public function delete(Field $field): void
    {
        $paths = $this->fieldRepository->deleteAllImages($field);

        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->fieldRepository->delete($field);
    }

    /**
     * Set the field status.
     */
    public function changeStatus(Field $field, FieldStatus $status): Field
    {
        $this->fieldRepository->updateStatus($field, $status);

        return $field->load('images');
    }
}
