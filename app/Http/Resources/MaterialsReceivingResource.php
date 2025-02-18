<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialsReceivingResource extends JsonResource
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
            'reference_code' => $this->reference_code,
            'terms_of_payment' => $this->terms_of_payment,
            'particulars' => $this->particulars,
            'transaction_date' => (new \DateTime($this->transaction_date))->format('F j, Y'),
            'equipment_no' => $this->equipment_no,
            'source_po' => $this->source_po,
            'total_net_of_vat_cost' => $this->total_net_of_vat_cost,
            'total_input_vat' => $this->total_input_vat,
            'grand_total' => $this->grand_total,
            'items' => MaterialsReceivingItemResource::collection($this->whenLoaded('items')),
            'warehouse' =>$this->warehouse->only(['id', 'name', 'location']),
            'supplier' =>$this->supplier->only(['id', 'supplier_code', 'company_name', 'company_address', 'company_email']),
            'project' => $this->project->only(['id', 'project_code', 'status']),
            "approvals" => $this->approvals,
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
