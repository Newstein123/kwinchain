<?php

namespace App\Enums;

enum FieldTimeSlotStatus: int
{
    case Available = 1;
    case Booked = 2;
    case Blocked = 0;
}
