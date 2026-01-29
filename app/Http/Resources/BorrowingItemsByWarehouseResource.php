<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowingItemsByWarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => optional($this->warehouse)->name,

            'date_time_borrowed' => $this->date_time_borrowed,
            'borrowed_by' => optional($this->borrowedBy)->fullname_first,
            'borrowed_contact_no' => $this->borrowed_contact_no,

            'returned_by' => optional($this->returnedBy)->fullname_first,
            'date_time_returned' => $this->date_time_returned,
            'received_by' => optional($this->receivedBy)->fullname_first,
            'remarks' => $this->remarks,
            'request_status' => $this->request_status,

            'items' => $this->items->map(function ($item) {
                $returned = $item->metadata['quantity_returned'] ?? 0;

                return [
                    'id' => $item->id,
                    'borrow_transaction_id' => $item->borrow_transaction_id,
                    'item_id' => $item->item_id,
                    'item_description' => $item->item->item_description ?? null,

                    'current_quantity' => (int) $item->quantity,
                    'quantity_returned' => (int) $returned,
                    'remaining_quantity' => (int) ($item->quantity - $returned),

                    'remarks' => $item->remarks,
                    'metadata' => $item->metadata,
                ];
            }),

            'approvals' => new ApprovalAttributeResource([
                'approvals' => $this->approvals,
            ]),

            'next_approval' => $this->getNextPendingApproval(),
        ];
    }
}
