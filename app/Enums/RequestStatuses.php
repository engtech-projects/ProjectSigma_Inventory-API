<?php

namespace App\Enums;

enum RequestStatuses: string
{
    case APPROVED = 'Approved';
    case PENDING = "Pending";
    case DENIED = "Denied";
    case CANCELLED = "Cancelled";
    case VOIDED = "Voided";

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->name] = $case->value;
        }
        return $array;
    }

    public static function toArraySwapped(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->name;
        }
        return $array;
    }
}
