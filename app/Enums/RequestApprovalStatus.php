<?php

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

// Statuses for Individual Approval
final class RequestApprovalStatus extends Enum
{
    public const APPROVED = "Approved";
    public const PENDING = "Pending";
    public const DENIED = "Denied";
}
