<?php

namespace App\Http\Resources\Api\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FieldTimeSlotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'field_id' => $this->field_id,
            'slot_date' => $this->slot_date?->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'price' => $this->price,
            'status' => $this->status?->value ?? $this->status,
            'block_reason' => $this->block_reason,
        ];
    }
}
