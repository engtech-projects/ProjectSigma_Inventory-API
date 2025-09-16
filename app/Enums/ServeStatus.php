<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum ServeStatus: string
{
    use EnumHelper;
    case SERVED = "Served";
    case UNSERVED = "Unserved";
}
