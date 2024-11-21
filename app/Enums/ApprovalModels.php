<?php

namespace App\Enums;

use App\Models\RequestItemProfiling;
use App\Models\RequestSupplier;

enum ApprovalModels: string
{
    case RequestItemProfiling = RequestItemProfiling::class;
    case RequestSupplier = RequestSupplier::class;

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
