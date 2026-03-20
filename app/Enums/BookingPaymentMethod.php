<?php

namespace App\Enums;

enum BookingPaymentMethod: int
{
    case Kpay = 0;
    case Wave = 1;
    case Bank = 2;
    case Cash = 3;
}
