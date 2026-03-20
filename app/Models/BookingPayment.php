<?php

namespace App\Models;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingPaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPayment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'payment_type',
        'payment_method',
        'amount',
        'proof_image',
        'status',
        'transaction_ref',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_type' => BookingPaymentType::class,
            'payment_method' => BookingPaymentMethod::class,
            'status' => BookingPaymentStatus::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
