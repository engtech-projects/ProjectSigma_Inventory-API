<?php

namespace App\Http\Services;

use App\Enums\ServeStatus;
use App\Models\BorrowTransaction;
use App\Models\RequestRequisitionSlip;
use App\Models\RequestTurnover;
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
    public function createMrrFromRequestTurnover(RequestTurnover $requestTurnover)
    {
        DB::transaction(function () use ($requestTurnover) {
            $this->model->warehouse_id = $requestTurnover->to_id;
            $this->model->reference_no = $this->generateNewMrrReferenceNumber();
            $this->model->supplier_id = null;
            $this->model->reference = $requestTurnover->reference_no;
            $this->model->terms_of_payment = null;
            $this->model->particulars = "Turnover from {$requestTurnover->from->name} to {$requestTurnover->to->name}";
            $this->model->transaction_date = now();
            $this->model->evaluated_by_id = null;
            $this->model->metadata = [
                'is_turnover' => true,
                'rt_id' => $requestTurnover->id,
                'from_warehouse_id' => $requestTurnover->from_warehouse_id,
                'to_warehouse_id' => $requestTurnover->to_warehouse_id,
                'turnover_date' => $requestTurnover->date,
            ];
            $this->model->save();

            $mappedItems = $requestTurnover->items->map(function ($item) use ($requestTurnover) {
                return [
                    'transaction_material_receiving_id' => $this->model->id,
                    'item_id' => $item->item_id,
                    'specification' => $item->condition ?? null,
                    'actual_brand_purchase' => null,
                    'requested_quantity' => $item->quantity,
                    'quantity' => $item->quantity,
                    'uom_id' => $item->uom,
                    'unit_price' => null,
                    'serve_status' => ServeStatus::UNSERVED,
                    'remarks' => $item->remarks ?? null,
                    'metadata' => [
                        'rt_id' => $requestTurnover->id,
                        'rt_item_id' => $item->id,
                        'original_condition' => $item->condition,
                        'from_warehouse_id' => $requestTurnover->from_warehouse_id,
                    ],
                ];
            });

            $this->model->items()->createMany($mappedItems->toArray());
        });

        return $this->model;
    }
    public function createMrrFromBorrowTransaction(BorrowTransaction $borrowTransaction)
    {
        DB::transaction(function () use ($borrowTransaction) {
            $this->model->warehouse_id   = $borrowTransaction->warehouse_id;
            $this->model->reference_no   = $this->generateNewMrrReferenceNumber();
            $this->model->supplier_id    = null;
            $this->model->reference      = $borrowTransaction->reference_no;
            $this->model->terms_of_payment = null;
            $this->model->particulars    = "Borrow transaction {$borrowTransaction->reference_no}";
            $this->model->transaction_date = now();
            $this->model->evaluated_by_id = null;
            $this->model->metadata = [
                'is_borrow_transaction' => true,
                'borrow_transaction_id' => $borrowTransaction->id,
                'borrowed_by' => $borrowTransaction->borrowed_by,
                'borrowed_contact_no' => $borrowTransaction->borrowed_contact_no,
                'date_time_borrowed' => $borrowTransaction->date_time_borrowed,
            ];

            $this->model->save();

            $items = $borrowTransaction->items->map(function ($item) use ($borrowTransaction) {
                $itemProfile = $item->item;

                if (!$itemProfile || !$itemProfile->uom) {
                    throw new \Exception(
                        "Item {$item->item_id} has no base UOM defined"
                    );
                }
                return [
                    'transaction_material_receiving_id' => $this->model->id,
                    'item_id' => $item->item_id,
                    'specification' => $itemProfile->item_description,
                    'actual_brand_purchase' => null,
                    'requested_quantity' => $item->quantity,
                    'quantity' => $item->quantity,
                    'uom_id' => $itemProfile->uom,
                    'unit_price' => null,
                    'serve_status' => ServeStatus::UNSERVED,
                    'remarks' => $item->remarks,
                    'metadata' => [
                        'borrow_transaction_id' => $borrowTransaction->id,
                        'borrow_transaction_item_id' => $item->id,
                    ],
                ];
            });

            $this->model->items()->createMany($items->toArray());
        });

        return $this->model;
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
