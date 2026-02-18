<?php

namespace App\Enums;

enum OwnerPayoutAccountStatus: int
{
    case Pending = 2;
    case Verified = 1;
}
