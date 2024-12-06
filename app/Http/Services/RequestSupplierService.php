<?php

namespace App\Http\Services;

use App\Models\RequestSupplier;

class RequestSupplierService
{
    public function applyFilters($query)
    {
        if (request()->has('company_name')) {
            $query->where('company_name', 'like', '%' . request()->input('company_name') . '%');
        }
        if (request()->has('type_of_ownership')) {
            $query->where('type_of_ownership', request()->input('type_of_ownership'));
        }
        if (request()->has('contact_person_name')) {
            $query->where('contact_person_name', 'like', '%' . request()->input('contact_person_name') . '%');
        }
        if (request()->has('supplier_code')) {
            $query->where('supplier_code', 'like', '%' . request()->input('supplier_code') . '%');
        }
        return $query;
    }

    public function getAll()
    {
        $query = RequestSupplier::query();
        $query = $this->applyFilters($query);
        return $query->get();
    }

    public function getMyRequest()
    {
        $query = RequestSupplier::with(['uploads'])->where('created_by', auth()->user()->id)->orderBy('created_at', 'DESC');
        $query = $this->applyFilters($query);
        return $query->paginate(10);
    }

    public function getAllRequest()
    {
        $query = RequestSupplier::with(['uploads'])->orderBy('created_at', 'DESC');
        $query = $this->applyFilters($query);
        return $query->paginate(10);
    }
    public function getAllApprovedRequest()
    {
        $query = RequestSupplier::where('request_status', 'Approved')->with(['uploads'])->orderBy('created_at', 'DESC');
        $query = $this->applyFilters($query);
        return $query->paginate(10);
    }

    public function getMyApprovals()
    {
        $userId = auth()->user()->id;

        $query = RequestSupplier::myApprovals()->with(['uploads'])->orderBy('created_at', 'DESC');
        $query = $this->applyFilters($query);

        $paginatedResults = $query->paginate(10);

        $filteredResults = $paginatedResults->getCollection()->filter(function ($item) use ($userId) {
            $nextPendingApproval = $item->getNextPendingApproval();
            return ($nextPendingApproval && $userId === (int)$nextPendingApproval['user_id']);
        });

        $paginatedResults->setCollection($filteredResults);

        return $paginatedResults;
    }
}
