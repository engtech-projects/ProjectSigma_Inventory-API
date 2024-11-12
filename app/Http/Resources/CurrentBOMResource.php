<?php

namespace App\Http\Resources;

use App\Models\Details;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrentBOMResource extends JsonResource
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
            'details' => Details::where('request_bom_id', $this->id)
                ->get(['id', 'request_bom_id', 'item_id', 'uom_id', 'unit_price', 'quantity'])
                ->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'request_bom_id' => $detail->request_bom_id,
                        'item_id' => $detail->item_id,
                        'item_summary' => $detail->getItemSummaryAttribute(),
                        'uom_id' => $detail->uom_id,
                        'unit_price' => $detail->unit_price,
                        'quantity' => $detail->quantity,
                    ];
                })->toArray(),
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}
