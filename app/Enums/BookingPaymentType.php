<?php

namespace App\Enums;

enum BookingPaymentType: int
{
    case Deposit = 0;
    case Full = 1;
    case Cash = 2;
}
