<?php

namespace App\Models;

use App\Enums\FieldSlotDuration;
use App\Enums\FieldStatus;
use App\Enums\FieldSurfaceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'location',
        'city',
        'address',
        'latitude',
        'longitude',
        'surface_type',
        'slot_duration',
        'price_per_hour',
        'deposit_amount',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'price_per_hour' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'surface_type' => FieldSurfaceType::class,
            'slot_duration' => FieldSlotDuration::class,
            'status' => FieldStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    /**
     * @return HasMany<FieldImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(FieldImage::class);
    }
}
