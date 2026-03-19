<?php

namespace App\Models;

use App\Enums\FieldTimeSlotStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldTimeSlot extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'field_id',
        'slot_date',
        'start_time',
        'end_time',
        'price',
        'status',
        'block_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot_date' => 'date',
            'price' => 'decimal:2',
            'status' => FieldTimeSlotStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Field, $this>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * @return HasMany<BookingSlot, $this>
     */
    public function bookingSlots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }
}
