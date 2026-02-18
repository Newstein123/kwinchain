<?php

namespace App\Models;

use App\Enums\OwnerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => OwnerStatus::class,
        ];
    }

    /**
     * @return HasMany<OwnerPayoutAccount, $this>
     */
    public function payoutAccounts(): HasMany
    {
        return $this->hasMany(OwnerPayoutAccount::class);
    }

    /**
     * @return HasMany<OwnerVerification, $this>
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(OwnerVerification::class);
    }
}
