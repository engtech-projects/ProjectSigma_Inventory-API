<?php

namespace App\Http\Resources;

use App\Models\RequestBOM;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\RequestStatuses;

class RequestStocksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'section_type' => $this->office_project,
            'current_bom' => new CurrentBOMResource(
                RequestBOM::where('assignment_type', $this->section_type)
                    ->where('assignment_id', $this->section_id)
                    ->where('request_status', RequestStatuses::APPROVED)
                    ->latest('version')
                    ->first()
            ),
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'uom_name' => $item->uom_name,
                    'convertable_units' => $item->convertable_units,
                    'item_id' => $item->item_id,
                    'item_description' => $item->item_description,
                    'specification' => $item->specification,
                    'preferred_brand' => $item->preferred_brand,
                    'reason' => $item->reason,
                    'location' => $item->location,
                    'location_qty' => $item->location_qty,
                    'is_approved' => $item->is_approved,
                    'type_of_request' => $item->type_of_request,
                    'contact_no' => $item->contact_no,
                    'remarks' => $item->remarks,
                    'current_smr' => $item->current_smr,
                    'previous_smr' => $item->previous_smr,
                    'unused_smr' => $item->unused_smr,
                    'next_smr' => $item->next_smr,
                ];
            })->toArray(),
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
