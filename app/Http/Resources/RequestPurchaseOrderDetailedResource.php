<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestPurchaseOrderDetailedResource extends JsonResource
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
            'transaction_date' => $this->transaction_date,
            'po_number' => $this->po_number,
            'request_canvass_summary_id' => $this->request_canvass_summary_id,
            'name_on_receipt' => $this->name_on_receipt,
            'delivered_to' => $this->delivered_to,
            'metadata' => $this->metadata,
            'processing_status' => $this->processing_status,
            'created_by' => $this->created_by_user_name,
            'supplier' => new RequestCanvassSummaryDetailedResource($this->whenLoaded('canvassSummary')),
        ];
    }
}
