<?php

namespace App\Http\Resources;

use App\Enums\TransactionTypes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseLogsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $movementIcon = in_array($this->transaction->transaction_type, [TransactionTypes::RETURN, TransactionTypes::RECEIVING]) ? '+' : '-';
        return
        [
            'transaction_date' => $this->transaction->transaction_date_human,
            'transaction_type' => $this->transaction->transaction_type,
            'item_code' => $this->item->item_code,
            'movement' => $movementIcon." ".$this->quantity." ". $this->item->uom_full_name,
        ];
    }
}
