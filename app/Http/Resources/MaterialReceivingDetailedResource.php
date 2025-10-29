<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialReceivingDetailedResource extends JsonResource
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
            'project_code' => $this->requisition_slip->project_department_name,
            'equipment_no' => $this->requisition_slip->equipment_no,
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->supplier,
            'supplier_name' => $this->supplier?->code_name,
            'reference' => $this->reference,
            'terms_of_payment' => $this->terms_of_payment,
            'particulars' => $this->particulars,
            'transaction_date' => $this->transaction_date,
            'evaluated_by' => $this->evaluated_by,
            'items' => MaterialReceivingItemDetailedResource::collection($this->items),
            'net_vat' => number_format($this->items->sum('net_vat'), 2),
            'input_vat' => number_format($this->items->sum('input_vat'), 2),
            'grand_total' => number_format($this->items->sum('grand_total'), 2),
            'warehouse_name' => $this->warehouse_name,
        ];
    }
}
