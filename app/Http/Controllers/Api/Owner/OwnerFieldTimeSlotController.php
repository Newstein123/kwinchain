<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\Field\BlockSlotRequest;
use App\Http\Requests\Api\Owner\Field\BulkBlockSlotsRequest;
use App\Http\Requests\Api\Owner\Field\GenerateSlotsRequest;
use App\Http\Requests\Api\Owner\Field\GetSlotsByDateRequest;
use App\Http\Requests\Api\Owner\Field\ManualSlotRequest;
use App\Http\Resources\Api\Owner\FieldTimeSlotResource;
use App\Services\Owner\OwnerFieldTimeSlotService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OwnerFieldTimeSlotController extends Controller
{
    public function __construct(
        private OwnerFieldTimeSlotService $slotService
    ) {}

    public function generate(GenerateSlotsRequest $request, int $id): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $result = $this->slotService->generateSlots(
                $owner,
                $id,
                $request->validated('start_date'),
                $request->validated('end_date')
            );

            return response()->json([
                'success' => true,
                'message' => 'Slots generated successfully',
                'data' => $result,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function manual(ManualSlotRequest $request, int $id): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $slot = $this->slotService->createManualSlot($owner, $id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Slot created successfully',
                'data' => new FieldTimeSlotResource($slot),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function listByDate(GetSlotsByDateRequest $request, int $id): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $slots = $this->slotService->getSlotsByDate($owner, $id, $request->validated('date'));

            return response()->json([
                'success' => true,
                'message' => 'Slots retrieved successfully',
                'data' => FieldTimeSlotResource::collection($slots),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function block(BlockSlotRequest $request, int $id): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $slot = $this->slotService->blockSlot($owner, $id, $request->validated('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Slot blocked successfully',
                'data' => new FieldTimeSlotResource($slot),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function unblock(int $id, Request $request): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $slot = $this->slotService->unblockSlot($owner, $id);

            return response()->json([
                'success' => true,
                'message' => 'Slot unblocked successfully',
                'data' => new FieldTimeSlotResource($slot),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function bulkBlock(BulkBlockSlotsRequest $request, int $id): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $result = $this->slotService->bulkBlock($owner, $id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Slots blocked successfully',
                'data' => $result,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function delete(int $id, Request $request): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $this->slotService->deleteSlot($owner, $id);

            return response()->json([
                'success' => true,
                'message' => 'Slot deleted successfully',
                'data' => null,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
