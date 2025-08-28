<?php

namespace App\Enums;
use App\Enums\Traits\EnumHelper;

enum FuelWithdrawal: string
{
    use EnumHelper;
    case FULL = 'Full Tank';
    case PARTIAL = 'Partial';
}
