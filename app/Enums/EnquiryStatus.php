<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case New = 'new';
    case Read = 'read';
    case InProgress = 'in_progress';
    case Closed = 'closed';
    case Spam = 'spam';
}
