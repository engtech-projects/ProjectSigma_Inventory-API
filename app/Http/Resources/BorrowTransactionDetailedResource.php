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
            'borrowed_by' => $this->borrowed_by,
            'returned_by' => $this->returned_by,
            'date_time_returned' => $this->date_time_returned,
            'created_at_human' => $this->createdAtDateHuman,
            'items' => $this->whenLoaded(
                'items',
                fn () => BorrowTransactionItemDetailedResource::collection($this->items)
            ),
        ];
    }
}
