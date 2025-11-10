<?php

namespace App\Http\Resources;

use App\Models\RequestRequisitionSlipItems;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsolidatedRequestItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "requisition_slip_id" => $this->requisitionSlip->id,
            "reference_no" => $this->requisitionSlip->reference_no,
            "request_for" => $this->requisitionSlip->request_for,
            "project_department_name" => $this->requisitionSlip->project_department_name,
            "warehouse_name" => $this->requisitionSlip->warehouse->name,
            "equipment_no" => $this->requisitionSlip->equipment_no,
            "date_prepared_human" => $this->requisitionSlip->date_prepared_human,
            "date_needed_human" => $this->requisitionSlip->date_needed_human,
            "status" => $this->requisitionSlip->request_status,
            "type_of_request" => $this->requisitionSlip->type_of_request,
            "remarks" => $this->requisitionSlip->remarks,
            "items" => RequestRequisitionSlipItems::where('request_requisition_slip_id', $this->requisitionSlip->id)->get()->map(function ($item) {
                return [
                    "id" => $item->id,
                    "item_id" => $item->item_id,
                    "item_description" => $item->itemProfile->item_description,
                    "specification" => $item->specification,
                    "preferred_brand" => $item->preferred_brand,
                    "quantity" => $item->quantity,
                    "unit" => $item->uom_name,
                    "reason" => $item->reason,
                    "project_department_name" => $this->requisitionSlip->project_department_name,
                ];
            }),
        ];
    }
}
