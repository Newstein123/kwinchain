<?php

namespace App\Models;

use App\Enums\OwnerTrustedUserLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerTrustedUser extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'user_id',
        'trust_level',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trust_level' => OwnerTrustedUserLevel::class,
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
