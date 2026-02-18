<?php

namespace App\Enums;

enum OwnerStatus: int
{
    case Pending = 2;
    case Verified = 1;
    case Suspended = 0;
}
