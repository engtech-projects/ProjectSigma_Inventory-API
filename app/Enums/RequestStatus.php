<?php

namespace App\Enums;

enum RequestStatus: string
{
    case APPROVE = "Approved";
    case DENIED = "Denied";
}
