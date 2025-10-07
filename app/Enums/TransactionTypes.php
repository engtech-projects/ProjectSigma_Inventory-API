<?php

namespace App\Enums;

enum TransactionTypes: string
{
    case RECEIVING = "Receiving";
    case TRANSFER = "Transfer";
    case WITHDRAW = "Withdraw";
    case RETURN = "Return";
}
