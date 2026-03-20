<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Field $this */
        $primaryImage = $this->images->firstWhere('is_primary', true)
            ?? $this->images->first();

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'location'       => $this->location,
            'description'    => $this->description,
            'price_per_hour' => $this->price_per_hour,
            'deposit_amount' => $this->deposit_amount,
            'status'         => $this->status?->value,
            'status_label'   => $this->status?->name,
            'image_url'      => $primaryImage
                ? asset('storage/'.$primaryImage->image_url)
                : null,
            'created_at'     => $this->created_at?->toDateTimeString(),
        ];
    }
}
