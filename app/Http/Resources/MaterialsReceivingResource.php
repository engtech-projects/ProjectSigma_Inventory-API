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
            // ...parent::toArray($request),
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'reference_code' => $this->reference_code,
            'terms_of_payment' => $this->terms_of_payment,
            'transaction_date' => $this->transaction_date,
            'equipment_no' => $this->equipment_no,
            'total_net_of_vat_cost' => $this->total_net_of_vat_cost,
            'total_input_vat' => $this->total_input_vat,
            'grand_total' => $this->grand_total,
            'particulars' => MaterialsReceivingItemResource::collection($this->whenLoaded('items')),
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'supplier' => $this->whenLoaded('supplier', function () {
                return $this->supplier->only(['id', 'supplier_code', 'company_name', 'company_address', 'company_email']);
            }),
            'project' => $this->whenLoaded('project', function () {
                return $this->project->only(['id', 'project_code', 'status']);
            }),
        ];
    }
}
