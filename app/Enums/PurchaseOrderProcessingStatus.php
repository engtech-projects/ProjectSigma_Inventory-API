<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum PurchaseOrderProcessingStatus: string
{
    use EnumHelper;
    case PENDING = "Pending";
    case PREPAYMENT = "Accounting for Prepayment";
    case ISSUED = "Issued to Supplier";
    case ITEMS_RECEIVED = "Items Received from Supplier";
    case CHANGES = "NCPO if any";
    case TURNED_OVER = "Turned Over to Requestor";
    case POSTPAYMENT = "Accounting for Postpayment";
    case SERVED = "Served";
}
