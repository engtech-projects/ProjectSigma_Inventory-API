<?php

namespace App\Http\Services;

use App\Models\RequestSupplier;

class RequestSupplierService
{
    public function getAll(array $filters = [])
    {
        $query = RequestSupplier::with('uploads');
        foreach ($filters as $key => $value) {
            if ($value) {
                if ($key === 'type_of_ownership') {
                    $query->whereIn($key, array_map('ucfirst', explode(',', $value)));
                } else {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }
        }
        return $query->get();
    }

    public function getMyRequest(array $filters = [])
    {
        $query = RequestSupplier::with(['uploads'])
            ->where("created_by", auth()->user()->id);
        foreach ($filters as $key => $value) {
            if ($value) {
                if ($key === 'type_of_ownership') {
                    $query->whereIn($key, array_map('ucfirst', explode(',', $value)));
                } else {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }
        }
        return $query->orderBy('created_at', 'DESC')->get();
    }
    public function getAllApprovedRequest(array $filters = [])
    {
        $query = RequestSupplier::where("request_status", "Approved")
            ->with(['uploads']);
        foreach ($filters as $key => $value) {
            if ($value) {
                if ($key === 'type_of_ownership') {
                    $query->whereIn($key, array_map('ucfirst', explode(',', $value)));
                } else {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }
        }
        return $query->orderBy('created_at', 'DESC')->get();
    }

    public function getAllRequests(array $filters = [])
    {
        $query = RequestSupplier::with(['uploads']);
        foreach ($filters as $key => $value) {
            if ($value) {
                if ($key === 'type_of_ownership') {
                    $query->whereIn($key, array_map('ucfirst', explode(',', $value)));
                } else {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }
        }
        return $query->orderBy('created_at', 'DESC')->get();
    }

    public function getMyApprovals(array $filters = [])
    {
        $userId = auth()->user()->id;

        $result = RequestSupplier::myApprovals()
            ->with(['uploads'])
            ->orderBy("created_at", "DESC")
            ->get();

        $result = $result->filter(function ($item) use ($userId, $filters) {
            $nextPendingApproval = $item->getNextPendingApproval();
            if ($nextPendingApproval && $userId !== $nextPendingApproval['user_id']) {
                return false;
            }
            foreach ($filters as $key => $value) {
                if ($value) {
                    if ($key === 'type_of_ownership') {
                        if (!in_array(ucfirst($item->$key), array_map('ucfirst', explode(',', $value)))) {
                            return false;
                        }
                    } elseif (strpos($item->$key, $value) === false) {
                        return false;
                    }
                }
            }
            return true;
        });

        return $result;
    }
}
