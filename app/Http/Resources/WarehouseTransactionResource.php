<?php

namespace App\Http\Resources;

use App\Models\Project;
use App\Models\RequestStock;
use App\Models\RequestSupplier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $supplier = $this->metadata['supplier_id'] ?? null;
        $project = $this->metadata['project_code'] ?? null;

        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'reference' => RequestStock::find($this->charging_id, ['id', 'reference_no']),
            'transaction_date' => $this->transaction_date,
            'transaction_type' => $this->transaction_type,
            'metadata' => $this->metadata,
            'charging_type' => $this->charging_type,
            'charging_id' => $this->charging_id,
            'created_by' => $this->created_by,
            'request_status' => $this->request_status,
            'items' => WarehouseTransactionItemResource::collection($this->items),
            'warehouse' => $this->warehouse->only(['id', 'name', 'location']),
            'supplier' => $supplier ? RequestSupplier::find($supplier, ['id', 'supplier_code', 'company_name', 'company_address']) : null,
            'project' => $project ? Project::find($project, ['id', 'project_code', 'status']) : null,
            "approvals" => new ApprovalAttributeResource(["approvals" => $this->approvals]),
            "next_approval" => $this->getNextPendingApproval(),
        ];
    }
}

