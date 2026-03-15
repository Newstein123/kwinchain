<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'verification_level' => $this->verification_level?->value ?? $this->verification_level,
            'status' => $this->status?->value ?? $this->status,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
        ];
    }
}
