<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowTransactionDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'date_time_borrowed' => $this->date_time_borrowed,
            'borrowed_by' => optional($this->borrowedBy)->fullname_first,
            'borrowed_contact_no' => $this->borrowed_contact_no,
            'returned_by' => optional($this->returnedBy)->fullname_first,
            'returned_contact_no' => $this->returned_contact_no,
            'date_time_returned' => $this->date_time_returned,
            'received_by' => optional($this->receivedBy)->fullname_first,
            'remarks' => $this->remarks,
            'created_at_human' => $this->createdAtDateHuman,
            'items' => $this->whenLoaded(
                'items',
                fn () => BorrowTransactionItemDetailedResource::collection($this->items)
            ),
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
