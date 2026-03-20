<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSlot extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'field_time_slot_id',
        'price',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return BelongsTo<FieldTimeSlot, $this>
     */
    public function fieldTimeSlot(): BelongsTo
    {
        return $this->belongsTo(FieldTimeSlot::class, 'field_time_slot_id');
    }
}
