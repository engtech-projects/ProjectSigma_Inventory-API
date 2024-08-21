<?php

namespace App\Enums;

enum RequestStatusType: string
{
    case APPROVED = 'Approved';
    case PENDING = "Pending";
    case DENIED = "Denied";
    case RELEASED = "Released";
}
