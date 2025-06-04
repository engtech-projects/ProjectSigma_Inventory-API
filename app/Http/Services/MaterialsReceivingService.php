<?php

namespace App\Http\Services;

use App\Models\MaterialsReceiving;
use App\Models\WarehouseTransaction;

class MaterialsReceivingService
{
    public function getAllRequest()
    {
        return WarehouseTransaction::with('items', 'warehouse')
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }

    public function getMyRequest()
    {
        return MaterialsReceiving::with(['items', 'project', 'supplier', 'project'])
        ->where("created_by", auth()->user()->id)
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }
    public function getAllApprovedRequest()
    {
        return MaterialsReceiving::where("request_status", "Approved")
        ->with(['items', 'project'])
        ->orderBy("created_at", "DESC")
        ->paginate(10);
    }

    public function getMyApprovals()
    {
        $userId = auth()->user()->id;

        $result = MaterialsReceiving::myApprovals()
                    ->with(['items', 'project'])
                    ->orderBy("created_at", "DESC")
                    ->paginate(10);

        return $result->filter(function ($item) use ($userId) {
            $nextPendingApproval = $item->getNextPendingApproval();
            return ($nextPendingApproval && $userId === $nextPendingApproval['user_id']);
        });
    }
}
