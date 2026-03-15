<?php

namespace App\Services;

use App\Enums\OwnerTrustedUserLevel;
use App\Models\Owner;
use App\Models\OwnerTrustedUser;
use App\Models\User;

class TrustedUserService
{
    /**
     * Mark a user as trusted under the given owner.
     * Uses updateOrCreate so the operation is idempotent.
     */
    public function markTrusted(Owner $owner, User $user): OwnerTrustedUser
    {
        return OwnerTrustedUser::updateOrCreate(
            ['owner_id' => $owner->id, 'user_id' => $user->id],
            ['trust_level' => OwnerTrustedUserLevel::Trusted],
        );
    }

    /**
     * Remove trust for a user under the given owner.
     * No-op (returns 0) if no record exists — callers treat this as success.
     */
    public function removeTrusted(Owner $owner, User $user): int
    {
        return OwnerTrustedUser::where([
            'owner_id' => $owner->id,
            'user_id'  => $user->id,
        ])->delete();
    }
}
