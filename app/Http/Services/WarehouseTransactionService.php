<?php

namespace App\Http\Services;

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
        return WarehouseTransaction::with(['items', 'project', 'supplier', 'project'])
        ->where("created_by", auth()->user()->id)
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }
    public function getAllApprovedRequest()
    {
        return WarehouseTransaction::where("request_status", "Approved")
        ->with(['items', 'project'])
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }

    public function getMyApprovals()
    {
        $userId = auth()->user()->id;

        $result = WarehouseTransaction::myApprovals()
                    ->with(['items', 'project'])
                    ->orderBy("created_at", "DESC")
                    ->paginate(10);

        return $result->filter(function ($item) use ($userId) {
            $nextPendingApproval = $item->getNextPendingApproval();
            return ($nextPendingApproval && $userId === $nextPendingApproval['user_id']);
        });
    }
}
