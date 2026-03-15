<?php

namespace App\Enums;

enum OwnerVerificationLevel: int
{
    case Basic = 1;
    case IdentityVerified = 2;
    case FullyVerified = 3;
}
