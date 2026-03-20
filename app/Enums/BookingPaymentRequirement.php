<?php

namespace App\Enums;

enum BookingPaymentRequirement: int
{
    case CashOnly = 0;
    case Deposit = 1;
    case Full = 2;
}
