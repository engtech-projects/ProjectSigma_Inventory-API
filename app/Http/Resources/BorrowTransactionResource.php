<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowTransactionResource extends JsonResource
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
            'borrowed_by' => $this->borrowed_by,
            'borrowed_contact_no' => $this->borrowed_contact_no,
            'returned_by' => $this->returned_by,
            'date_time_returned' => $this->date_time_returned,
            'received_by' => $this->received_by,
            'remarks' => $this->remarks,
            'items' => $this->whenLoaded(
                'items',
                fn () => BorrowTransactionItemDetailedResource::collection($this->items)
            ),
        ];
    }
}
