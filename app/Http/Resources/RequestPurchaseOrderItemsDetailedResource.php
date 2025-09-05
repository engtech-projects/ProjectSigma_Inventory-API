<?php

namespace App\Http\Resources;

use App\Http\Services\NcpoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestPurchaseOrderItemsDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * This resource computes the final purchase order details with all NCPO changes applied
     */
    public function toArray(Request $request): array
    {
        $ncpoService = app(NcpoService::class);

        // Get computed items with all NCPO changes applied
        $computedItems = $ncpoService->getComputedPurchaseOrderItems($this->resource);
        $variance = $ncpoService->getPurchaseOrderVariance($this->resource);

        return [
            'id' => $this->id,
            'po_number' => $this->po_number,
            'date' => $this->date,
            'processing_status' => $this->processing_status,
            'supplier' => [
                'id' => $this->supplier_id,
                'name' => $this->supplier?->company_name,
                'address' => $this->supplier?->company_address,
            ],
            'delivery_info' => [
                'delivery_address' => $this->delivery_address,
                'delivery_date' => $this->delivery_date,
                'delivery_terms' => $this->requestCanvassSummary?->delivery_terms,
            ],
            'payment_info' => [
                'terms_of_payment' => $this->requestCanvassSummary?->terms_of_payment,
                'availability' => $this->requestCanvassSummary?->availability,
            ],

            // Original PO data (before NCPO changes)
            'original_data' => [
                'total_amount' => $this->requestCanvassSummary?->grand_total_amount ?? 0,
                'items_count' => $this->requestCanvassSummary?->items?->count() ?? 0,
            ],

            // Computed data (with NCPO changes applied)
            'computed_data' => [
                'total_amount' => $variance['computed_total'],
                'items_count' => $computedItems->count(),
                'active_items_count' => $computedItems->where('is_cancelled', false)->count(),
                'cancelled_items_count' => $computedItems->where('is_cancelled', true)->count(),
            ],

            // Variance information
            'variance' => [
                'amount_difference' => $variance['variance_amount'],
                'percentage_difference' => round($variance['variance_percentage'], 2),
                'has_changes' => $computedItems->where('has_changes', true)->count() > 0,
                'total_ncpos_applied' => $this->ncpos()->where('request_status', 'approved')->count(),
            ],

            // Detailed items with original vs computed values
            'items' => $computedItems->map(function ($item) {
                return [
                    'item_id' => $item['item_id'],
                    'item_profile' => [
                        'id' => $item['item_profile']?->id,
                        'item_name' => $item['item_profile']?->item_name,
                        'item_code' => $item['item_profile']?->item_code,
                        'item_description' => $item['item_profile']?->item_description,
                    ],

                    // Original values (from canvass summary and requisition slip)
                    'original' => [
                        'quantity' => $item['original_quantity'],
                        'unit_price' => number_format($item['original_unit_price'], 2),
                        'total_amount' => number_format($item['original_total_amount'], 2),
                        'specification' => $item['original_specification'],
                        'preferred_brand' => $item['original_preferred_brand'],
                        'uom_id' => $item['original_uom_id'],
                    ],

                    // Computed values (with NCPO changes applied)
                    'computed' => [
                        'quantity' => $item['computed_quantity'],
                        'unit_price' => number_format($item['computed_unit_price'], 2),
                        'total_amount' => number_format($item['computed_total_amount'], 2),
                        'specification' => $item['computed_specification'],
                        'preferred_brand' => $item['computed_preferred_brand'],
                        'uom_id' => $item['computed_uom_id'],
                    ],

                    // Change information
                    'changes' => [
                        'has_changes' => $item['has_changes'],
                        'is_cancelled' => $item['is_cancelled'],
                        'quantity_changed' => $item['original_quantity'] != $item['computed_quantity'],
                        'price_changed' => $item['original_unit_price'] != $item['computed_unit_price'],
                        'specification_changed' => $item['original_specification'] != $item['computed_specification'],
                        'brand_changed' => $item['original_preferred_brand'] != $item['computed_preferred_brand'],
                        'uom_changed' => $item['original_uom_id'] != $item['computed_uom_id'],
                        'amount_variance' => $item['computed_total_amount'] - $item['original_total_amount'],
                    ],

                    // NCPO history for this item
                    'ncpo_history' => $item['ncpo_changes'],
                ];
            })->toArray(),

            // Applied NCPOs summary
            'applied_ncpos' => $this->ncpos()
                ->where('request_status', 'approved')
                ->with('items')
                ->get()
                ->map(function ($ncpo) {
                    return [
                        'id' => $ncpo->id,
                        'ncpo_no' => $ncpo->ncpo_no,
                        'date' => $ncpo->date,
                        'justification' => $ncpo->justification,
                        'items_affected' => $ncpo->items->count(),
                        'items_cancelled' => $ncpo->items->where('cancel_item', true)->count(),
                        'total_impact' => number_format($ncpo->new_po_total - $ncpo->items->sum('original_total'), 2),
                    ];
                })->toArray(),

            'metadata' => [
                'computed_at' => now()->toISOString(),
                'has_pending_ncpos' => $this->ncpos()->where('request_status', 'pending')->exists(),
                'last_ncpo_applied' => $this->ncpos()
                    ->where('request_status', 'approved')
                    ->latest()
                    ->value('date'),
            ],
        ];
    }
}
