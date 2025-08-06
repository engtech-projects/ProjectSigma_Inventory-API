<?php

namespace App\Http\Services;

use App\Enums\ServeStatus;
use App\Models\RequestRequisitionSlip;
use App\Models\TransactionMaterialReceiving;
use Illuminate\Support\Facades\DB;

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
            $this->model->warehouse_id = $requestRequisitionSlip->warehouse_id;
            $this->model->reference_no = $this->generateNewMrrReferenceNumber();
            $this->model->supplier_id = null;
            $this->model->reference = null;
            $this->model->terms_of_payment = null;
            $this->model->particulars = null;
            $this->model->transaction_date = now();
            $this->model->evaluated_by_id = null;
            $this->model->metadata = [
                'is_petty_cash' => true,
                'rs_id' => $requestRequisitionSlip->id
            ];
            $mappedItems = $requestRequisitionSlip->items->map(function ($item) use ($requestRequisitionSlip) {
                return [
                    'item_id' => $item->item_id,
                    'specification' => $item->specification,
                    'actual_brand_purchased' => null,
                    'requested_quantity' => $item->quantity,
                    'quantity' => $item->quantity,
                    'uom_id' => $item->unit,
                    'unit_price' => null,
                    'serve_status' => ServeStatus::UNSERVED,
                    'remarks' => null,
                    'metadata' => [
                        'rs_id' => $requestRequisitionSlip->id,
                        'rs_item_id' => $item->id
                    ],
                ];
            });
            $this->model->items()->createMany($mappedItems->toArray());
            $this->model->save();
        });
    }
    private function generateNewMrrReferenceNumber()
    {
        $year = now()->year;
        $lastMRR = TransactionMaterialReceiving::whereYear('created_at', $year)
            ->where('reference_no', 'like', "MRR-{$year}-%")
            ->orderBy('reference_no', 'desc')
            ->first();
        $lastNumber = 0;
        if ($lastMRR) {
            $lastNumber = (int) substr($lastMRR->reference_no, -4);
            $newNumber = str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "MRR-{$year}-{$newNumber}";
    }
}
