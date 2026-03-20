<?php

namespace App\Models;

use App\Enums\OwnerStatus;
use App\Enums\OwnerVerificationLevel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Owner extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens;

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
        'verification_level',
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
            'verification_level' => OwnerVerificationLevel::class,
            'email_verified_at' => 'datetime',
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

    /**
     * @return HasMany<OwnerTrustedUser, $this>
     */
    public function trustedUsers(): HasMany
    {
        return $this->hasMany(OwnerTrustedUser::class);
    }
}
