<?php

namespace App\Enums;

enum BookingQrScanStatus: int
{
    case Invalid = 0;
    case Success = 1;
    case AlreadyUsed = 2;
    case Expired = 3;
}
