<?php

namespace App\Enums;

enum OwnerVerificationStatus: int
{
    case Pending = 2;
    case Approved = 1;
    case Rejected = 0;
}
