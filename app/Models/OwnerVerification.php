<?php

namespace App\Models;

use App\Enums\OwnerVerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerVerification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'nrc_number',
        'nrc_front_image',
        'nrc_back_image',
        'selfie_with_id',
        'business_name',
        'business_license_image',
        'land_document_image',
        'utility_bill_image',
        'field_location_lat',
        'field_location_lng',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OwnerVerificationStatus::class,
            'reviewed_at' => 'datetime',
            'field_location_lat' => 'float',
            'field_location_lng' => 'float',
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
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
