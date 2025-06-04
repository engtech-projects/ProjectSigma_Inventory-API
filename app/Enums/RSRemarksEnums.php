<?php

namespace App\Enums;

enum RSRemarksEnums: string
{
    case PURCHASEORDER = "Purchase Order";
    case CANCELLED = "Cancelled";
    case PURCHASEORDERMR = "Purchase Order for MR";
    case PETTYCASH = "Petty Cash";
}
