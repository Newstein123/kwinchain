<?php

namespace App\Http\Controllers\Api;

use App\Enums\FieldStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFieldRequest;
use App\Http\Requests\Api\UpdateFieldRequest;
use App\Http\Resources\Api\FieldResource;
use App\Models\Field;
use App\Models\Owner;
use App\Services\FieldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerFieldController extends Controller
{
    public function __construct(
        private readonly FieldService $fieldService,
    ) {}

    /**
     * GET /api/v1/owner/fields
     *
     * List all fields belonging to the authenticated owner.
     */
    public function index(Request $request): JsonResponse
    {
        $owner = $this->resolveOwner($request);

        if (! $owner) {
            return response()->json(['message' => 'Owner profile not found.'], 404);
        }

        $fields = $this->fieldService->listForOwner($owner);

        return response()->json([
            'message' => 'Fields retrieved successfully.',
            'data'    => FieldResource::collection($fields),
        ]);
    }

    /**
     * POST /api/v1/owner/fields
     *
     * Create a new football field.
     */
    public function store(StoreFieldRequest $request): JsonResponse
    {
        $owner = $this->resolveOwner($request);

        if (! $owner) {
            return response()->json(['message' => 'Owner profile not found.'], 404);
        }

        $field = $this->fieldService->create(
            owner: $owner,
            data: $request->validated(),
            image: $request->file('image'),
        );

        return response()->json([
            'message' => 'Field created successfully.',
            'data'    => new FieldResource($field),
        ], 201);
    }

    /**
     * GET /api/v1/owner/fields/{field}
     *
     * Show a single field owned by the authenticated owner.
     */
    public function show(Request $request, Field $field): JsonResponse
    {
        $owner = $this->resolveOwner($request);

        if (! $owner || $field->owner_id !== $owner->id) {
            return response()->json(['message' => 'Field not found.'], 404);
        }

        $field->load('images');

        return response()->json([
            'message' => 'Field retrieved successfully.',
            'data'    => new FieldResource($field),
        ]);
    }

    /**
     * PUT/PATCH /api/v1/owner/fields/{field}
     *
     * Update an existing field.
     */
    public function update(UpdateFieldRequest $request, Field $field): JsonResponse
    {
        $owner = $this->resolveOwner($request);

        if (! $owner || $field->owner_id !== $owner->id) {
            return response()->json(['message' => 'Field not found.'], 404);
        }

        $field = $this->fieldService->update(
            field: $field,
            data: $request->validated(),
            image: $request->file('image'),
        );

        return response()->json([
            'message' => 'Field updated successfully.',
            'data'    => new FieldResource($field),
        ]);
    }

    /**
     * DELETE /api/v1/owner/fields/{field}
     *
     * Delete a field. Only allowed if it has no bookings.
     */
    public function destroy(Request $request, Field $field): JsonResponse
    {
        $owner = $this->resolveOwner($request);

        if (! $owner || $field->owner_id !== $owner->id) {
            return response()->json(['message' => 'Field not found.'], 404);
        }

        // Prevent deletion if bookings exist
        // (uncomment when Booking model is introduced)
        // if ($field->bookings()->exists()) {
        //     return response()->json(['message' => 'Cannot delete a field that has bookings.'], 409);
        // }

        $this->fieldService->delete($field);

        return response()->json(['message' => 'Field deleted successfully.']);
    }

    /**
     * PATCH /api/v1/owner/fields/{field}/status
     *
     * Toggle the field status between Active and Inactive.
     */
    public function changeStatus(Request $request, Field $field): JsonResponse
    {
        $owner = $this->resolveOwner($request);

        if (! $owner || $field->owner_id !== $owner->id) {
            return response()->json(['message' => 'Field not found.'], 404);
        }

        $request->validate([
            'status' => ['required', 'in:0,1'],
        ]);

        $status = (int) $request->input('status') === 1
            ? FieldStatus::Active
            : FieldStatus::Inactive;

        $field = $this->fieldService->changeStatus($field, $status);

        return response()->json([
            'message' => 'Field status updated successfully.',
            'data'    => new FieldResource($field),
        ]);
    }

    /**
     * Resolve the Owner record linked to the authenticated user.
     * Owners and Users are matched by email.
     */
    private function resolveOwner(Request $request): ?Owner
    {
        $user = $request->user();

        return Owner::where('email', $user->email)->first();
    }
}
