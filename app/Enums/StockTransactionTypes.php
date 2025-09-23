<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum StockTransactionTypes: string
{
    use EnumHelper;
    case STOCKIN = "Stock In";
    case STOCKOUT = "Stock Out";
}
