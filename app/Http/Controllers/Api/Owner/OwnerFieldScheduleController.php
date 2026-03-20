<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\Field\AddWeeklyScheduleRequest;
use App\Http\Requests\Api\Owner\Field\UpdateWeeklyScheduleRequest;
use App\Http\Resources\Api\Owner\FieldScheduleResource;
use App\Services\Owner\OwnerFieldScheduleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OwnerFieldScheduleController extends Controller
{
    public function __construct(
        private OwnerFieldScheduleService $scheduleService
    ) {}

    public function add(AddWeeklyScheduleRequest $request, int $id): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $schedule = $this->scheduleService->addWeeklySchedule($owner, $id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Schedule added successfully',
                'data' => new FieldScheduleResource($schedule),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(UpdateWeeklyScheduleRequest $request, int $id): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $schedule = $this->scheduleService->updateSchedule($owner, $id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'data' => new FieldScheduleResource($schedule),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function list(int $id, Request $request): JsonResponse
    {
        try {
            $owner = $request->user('owner');
            $schedules = $this->scheduleService->listSchedules($owner, $id);

            return response()->json([
                'success' => true,
                'message' => 'Schedules retrieved successfully',
                'data' => FieldScheduleResource::collection($schedules),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
