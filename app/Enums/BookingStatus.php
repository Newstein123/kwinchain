<?php

namespace App\Enums;

enum BookingStatus: int
{
    case Pending = 0;
    case Confirmed = 1;
    case CheckedIn = 2;
    case Completed = 3;
    case Cancelled = 4;
    case Expired = 5;
}
