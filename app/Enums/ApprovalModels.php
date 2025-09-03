<?php

namespace App\Enums;

use App\Models\RequestItemProfiling;
use App\Models\RequestSupplier;
use App\Models\RequestBOM;
use App\Models\RequestRequisitionSlip;
use App\Models\RequestCanvassSummary;
use App\Models\RequestNCPO;

enum ApprovalModels: string
{
    case RequestItemProfiling = RequestItemProfiling::class;
    case RequestSupplier = RequestSupplier::class;
    case RequestRequisitionSlip = RequestRequisitionSlip::class;
    case RequestBOM = RequestBOM::class;
    case RequestCanvassSummary = RequestCanvassSummary::class;
    case RequestNCPO = RequestNCPO::class;

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
