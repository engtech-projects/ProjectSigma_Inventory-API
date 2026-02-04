<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum ReceivingAcceptanceStatus: string
{
    use EnumHelper;
    case PENDING = "Pending";
    case ACCEPTED = "Accepted";
    case REJECTED = "Rejected";
}
