<?php

namespace App\Http\Services;

use App\Enums\RequestStatuses;
use App\Models\WarehouseTransaction;

class WarehouseTransactionService
{
    public function getAllRequest()
    {
        return WarehouseTransaction::with(['items.uomRelationship', 'items.item', 'warehouse'])
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }

    public function getMyRequest()
    {
        return WarehouseTransaction::with(['items.uomRelationship', 'items.item', 'warehouse'])
        ->myRequests()
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }
    public function getAllApprovedRequest()
    {
        return WarehouseTransaction::where("request_status", RequestStatuses::APPROVED)
        ->with(['items.uomRelationship', 'items.item', 'warehouse'])
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }

    public function getMyApprovals()
    {
        $result = WarehouseTransaction::myApprovals()
                    ->with(['items.uomRelationship', 'items.item', 'warehouse'])
                    ->orderBy("created_at", "DESC")
                    ->paginate(10);

        return $result;
    }
}
