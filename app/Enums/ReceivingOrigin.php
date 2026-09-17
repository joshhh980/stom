<?php

namespace App\Enums;

enum ReceivingOrigin: int
{
    case PURCHASE_ORDER = 1;
    case BACK_ORDER = 2;
}
