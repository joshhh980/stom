<?php

namespace App\Enums;

enum UserType: int
{
    case STANDARD = 0;
    case ADMIN = 1;
}