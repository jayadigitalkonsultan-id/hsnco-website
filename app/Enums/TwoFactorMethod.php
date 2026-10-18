<?php

namespace App\Enums;

enum TwoFactorMethod: string
{
    case Totp = 'totp';
    case Email = 'email';
}
