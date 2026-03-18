<?php

namespace App\Models;

use App\Enums\BookingPaymentRequirement;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'field_id',
        'booking_date',
        'start_time',
        'end_time',
        'payment_requirement',
        'status',
        'subtotal',
        'discount_amount',
        'reward_amount',
        'total_price',
        'paid_amount',
        'qr_token',
        'qr_generated_at',
        'expires_at',
        'checked_in_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'payment_requirement' => BookingPaymentRequirement::class,
            'status' => BookingStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'reward_amount' => 'decimal:2',
            'total_price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'qr_generated_at' => 'datetime',
            'expires_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }

    /**
     * @return HasMany<BookingPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    /**
     * @return HasMany<BookingQrLog, $this>
     */
    public function qrLogs(): HasMany
    {
        return $this->hasMany(BookingQrLog::class);
    }
}
