<?php

namespace App\Enums;

enum BookingPaymentStatus: int
{
    case Pending = 0;
    case Submitted = 1;
    case Verified = 2;
    case Paid = 3;
    case Rejected = 4;
}
