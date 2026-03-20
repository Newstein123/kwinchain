<?php

namespace App\Models;

use App\Enums\BookingQrScanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingQrLog extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'scanned_by',
        'scanned_at',
        'qr_token',
        'ip_address',
        'device_info',
        'scan_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'scan_status' => BookingQrScanStatus::class,
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
     * @return BelongsTo<Owner, $this>
     */
    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(Owner::class, 'scanned_by');
    }
}
