<?php

namespace App\Enums;

enum StringRequestApprovalStatus: string
{
    case APPROVED = 'Approved';
    case PENDING = "Pending";
    case DENIED = "Denied";
}
