<?php

namespace App\Http\Services;

use App\Models\RequestRequisitionSlip;
use App\Models\TransactionMaterialReceiving;

class MrrService
{
    protected $model;
    public function __construct(TransactionMaterialReceiving $model)
    {
        $this->model = $model;
    }
    public function createPettyCashMrrFromRequestRequisitionSlip(RequestRequisitionSlip $requestRequisitionSlip)
    {
        DB::transaction(function () use ($requestRequisitionSlip) {

        })
    }
    private function generateMRRReferenceNumber()
    {
        $year = now()->year;
        $lastMRR = TransactionMaterialReceiving::whereYear('created_at', $year)
            ->where('reference_no', 'like', "MRR-{$year}-%")
            ->orderBy('reference_no', 'desc')
            ->first();
        if ($lastMRR) {
            $lastNumber = (int) substr($lastMRR->reference_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        return "MRR-{$year}-CENTRAL-{$newNumber}";
    }
}
