<?php

namespace App\Enums;

enum UserStatus: string
{
    case Invited = 'invited';
    case PendingTwoFactor = 'pending_2fa';
    case Active = 'active';
    case Inactive = 'inactive';
}
