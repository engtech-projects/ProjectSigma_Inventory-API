<?php

namespace App\Http\Services;

use App\Enums\ServeStatus;
use App\Models\RequestRequisitionSlip;
use App\Models\TransactionMaterialReceiving;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            Log::info($requestRequisitionSlip);
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
            $this->model->save();
            $mappedItems = $requestRequisitionSlip->items->map(function ($item) use ($requestRequisitionSlip) {
                return [
                    'transaction_material_receiving_id' => $this->model->id,
                    'item_id' => $item->item_id,
                    'specification' => $item->specification,
                    'actual_brand_purchase' => null,
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
        });
    }
    private function generateNewMrrReferenceNumber()
    {
        $year = now()->year;
        $lastMRR = TransactionMaterialReceiving::orderByRaw('SUBSTRING_INDEX(reference_no, \'-\', -1) DESC')
            ->first();
        $lastRefNo = $lastMRR ? collect(explode('-', $lastMRR->reference_no))->last() : 0;
        $newNumber = str_pad($lastRefNo + 1, 6, '0', STR_PAD_LEFT);
        return "MRR-{$year}-{$newNumber}";
    }
}
