<?php

namespace App\Enums;

enum OwnerTrustedUserLevel: int
{
    case Trusted = 1;
    case Vip = 2;
    case Banned = 0;
}
