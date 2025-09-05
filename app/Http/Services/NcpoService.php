<?php

namespace App\Http\Services;

use App\Models\RequestNCPO;
use App\Models\RequestPurchaseOrder;
use App\Models\RequestCanvassSummaryItems;
use App\Models\RequestRequisitionSlipItems;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NcpoService
{
    /**
     * This service now focuses on computing the final purchase order details
     * by applying NCPO changes on top of original data WITHOUT modifying source tables
     */

    /**
     * Get the computed purchase order items with NCPO changes applied
     * This returns the final state without modifying original data
     */
    public function getComputedPurchaseOrderItems(RequestPurchaseOrder $purchaseOrder): Collection
    {
        $purchaseOrder->load([
            'requestCanvassSummary.items.itemProfile',
            'requestCanvassSummary.priceQuotation.requestProcurement.requisitionSlip.items',
            'ncpos.items' => function ($query) {
                $query->whereHas('requestNcpo', function ($q) {
                    $q->where('request_status', 'approved');
                });
            }
        ]);

        $canvassSummary = $purchaseOrder->requestCanvassSummary;
        $originalItems = $canvassSummary->items;

        // Get all approved NCPO changes for this PO
        $approvedNcpoChanges = $this->getApprovedNcpoChanges($purchaseOrder);

        return $originalItems->map(function ($canvassSummaryItem) use ($approvedNcpoChanges) {
            return $this->computeItemWithChanges($canvassSummaryItem, $approvedNcpoChanges);
        })->filter(function ($item) {
            // Filter out cancelled items (quantity = 0)
            return $item['computed_quantity'] > 0;
        });
    }

    /**
     * Get all approved NCPO changes for a purchase order
     */
    private function getApprovedNcpoChanges(RequestPurchaseOrder $purchaseOrder): Collection
    {
        return $purchaseOrder->ncpos()
            ->where('request_status', 'approved')
            ->with('items')
            ->get()
            ->flatMap(function ($ncpo) {
                return $ncpo->items->map(function ($item) use ($ncpo) {
                    $item->ncpo_date = $ncpo->date;
                    $item->ncpo_no = $ncpo->ncpo_no;
                    return $item;
                });
            })
            ->groupBy('item_id');
    }

    /**
     * Compute the final item details with all NCPO changes applied
     */
    private function computeItemWithChanges($canvassSummaryItem, Collection $ncpoChangesByItem): array
    {
        $itemId = $canvassSummaryItem->item_id;
        $requisitionSlipItem = $canvassSummaryItem->requisitionSlipItem;

        // Start with original values
        $computed = [
            'item_id' => $itemId,
            'item_profile' => $canvassSummaryItem->itemProfile,
            'original_quantity' => $requisitionSlipItem?->quantity ?? 0,
            'original_unit_price' => $canvassSummaryItem->unit_price ?? 0,
            'original_specification' => $requisitionSlipItem?->specification,
            'original_preferred_brand' => $requisitionSlipItem?->preferred_brand,
            'original_uom_id' => $requisitionSlipItem?->unit,
            'original_total_amount' => ($requisitionSlipItem?->quantity ?? 0) * ($canvassSummaryItem->unit_price ?? 0),

            // These will be computed with changes applied
            'computed_quantity' => $requisitionSlipItem?->quantity ?? 0,
            'computed_unit_price' => $canvassSummaryItem->unit_price ?? 0,
            'computed_specification' => $requisitionSlipItem?->specification,
            'computed_preferred_brand' => $requisitionSlipItem?->preferred_brand,
            'computed_uom_id' => $requisitionSlipItem?->unit,
            'computed_total_amount' => 0,

            'is_cancelled' => false,
            'ncpo_changes' => [],
            'has_changes' => false,
        ];

        // Apply NCPO changes if any exist for this item
        $itemChanges = $ncpoChangesByItem->get($itemId, collect());

        if ($itemChanges->isNotEmpty()) {
            $computed = $this->applyNcpoChangesToItem($computed, $itemChanges);
        }

        // Calculate final total amount
        $computed['computed_total_amount'] = $computed['computed_quantity'] * $computed['computed_unit_price'];

        return $computed;
    }

    /**
     * Apply all NCPO changes for a specific item (in chronological order)
     */
    private function applyNcpoChangesToItem(array $computed, Collection $itemChanges): array
    {
        // Sort changes by NCPO date to apply them in correct order
        $sortedChanges = $itemChanges->sortBy('ncpo_date');

        foreach ($sortedChanges as $change) {
            $computed['ncpo_changes'][] = [
                'ncpo_id' => $change->request_ncpo_id,
                'ncpo_no' => $change->ncpo_no,
                'ncpo_date' => $change->ncpo_date,
                'changes_made' => $this->getChangesSummary($change),
            ];

            // Apply the change
            if ($change->cancel_item) {
                $computed['is_cancelled'] = true;
                $computed['computed_quantity'] = 0;
                $computed['has_changes'] = true;
                break; // No need to apply further changes if cancelled
            }

            // Apply quantity change
            if ($change->changed_qty !== null) {
                $computed['computed_quantity'] = $change->changed_qty;
                $computed['has_changes'] = true;
            }

            // Apply unit price change
            if ($change->changed_unit_price !== null) {
                $computed['computed_unit_price'] = $change->changed_unit_price;
                $computed['has_changes'] = true;
            }

            // Apply specification change
            if ($change->changed_specification !== null) {
                $computed['computed_specification'] = $change->changed_specification;
                $computed['has_changes'] = true;
            }

            // Apply brand change
            if ($change->changed_brand !== null) {
                $computed['computed_preferred_brand'] = $change->changed_brand;
                $computed['has_changes'] = true;
            }

            // Apply UOM change
            if ($change->changed_uom_id !== null) {
                $computed['computed_uom_id'] = $change->changed_uom_id;
                $computed['has_changes'] = true;
            }
        }

        return $computed;
    }

    /**
     * Get summary of what changed in an NCPO item
     */
    private function getChangesSummary($ncpoItem): array
    {
        $changes = [];

        if ($ncpoItem->cancel_item) {
            $changes[] = 'Item cancelled';
        }

        if ($ncpoItem->changed_qty !== null) {
            $changes[] = "Quantity changed to {$ncpoItem->changed_qty}";
        }

        if ($ncpoItem->changed_unit_price !== null) {
            $changes[] = "Unit price changed to " . number_format($ncpoItem->changed_unit_price, 2);
        }

        if ($ncpoItem->changed_specification !== null) {
            $changes[] = "Specification updated";
        }

        if ($ncpoItem->changed_brand !== null) {
            $changes[] = "Brand changed to {$ncpoItem->changed_brand}";
        }

        if ($ncpoItem->changed_uom_id !== null) {
            $changes[] = "UOM changed";
        }

        if ($ncpoItem->changed_supplier_id !== null) {
            $changes[] = "Supplier changed";
        }

        return $changes;
    }

    /**
     * Get the total amount for a purchase order with all NCPO changes applied
     */
    public function getComputedPurchaseOrderTotal(RequestPurchaseOrder $purchaseOrder): float
    {
        return $this->getComputedPurchaseOrderItems($purchaseOrder)
            ->sum('computed_total_amount');
    }

    /**
     * Get variance between original and computed totals
     */
    public function getPurchaseOrderVariance(RequestPurchaseOrder $purchaseOrder): array
    {
        $computedItems = $this->getComputedPurchaseOrderItems($purchaseOrder);
        $originalTotal = $computedItems->sum('original_total_amount');
        $computedTotal = $computedItems->sum('computed_total_amount');

        return [
            'original_total' => $originalTotal,
            'computed_total' => $computedTotal,
            'variance_amount' => $computedTotal - $originalTotal,
            'variance_percentage' => $originalTotal > 0 ? (($computedTotal - $originalTotal) / $originalTotal) * 100 : 0,
        ];
    }

    /**
     * Get detailed comparison for reporting purposes
     */
    public function getDetailedComparison(RequestPurchaseOrder $purchaseOrder): array
    {
        $computedItems = $this->getComputedPurchaseOrderItems($purchaseOrder);
        $variance = $this->getPurchaseOrderVariance($purchaseOrder);

        return [
            'purchase_order_id' => $purchaseOrder->id,
            'po_number' => $purchaseOrder->po_number,
            'items' => $computedItems->toArray(),
            'summary' => $variance,
            'total_items' => $computedItems->count(),
            'items_with_changes' => $computedItems->where('has_changes', true)->count(),
            'cancelled_items' => $computedItems->where('is_cancelled', true)->count(),
        ];
    }

    /**
     * This method is called when NCPO is approved - but now it just logs the approval
     * The actual computation happens dynamically via the resource
     */
    public function createNcpoChangesToPurchaseOrder(RequestNCPO $ncpo): void
    {
        // Instead of modifying data, we just log that this NCPO was applied
        Log::info("NCPO Approved and Ready for Application", [
            'ncpo_id' => $ncpo->id,
            'ncpo_no' => $ncpo->ncpo_no,
            'po_id' => $ncpo->po_id,
            'approved_at' => now()->toISOString(),
            'items_count' => $ncpo->items->count(),
        ]);

        // The actual application happens dynamically when the PurchaseOrderItemDetailedResource
        // calls the NcpoService to get computed values
    }

    /**
     * Validate that NCPO changes are logically consistent
     */
    public function validateNcpoChanges(RequestNCPO $ncpo): array
    {
        $errors = [];
        $warnings = [];

        foreach ($ncpo->items as $item) {
            // Check for negative quantities
            if ($item->changed_qty !== null && $item->changed_qty < 0) {
                $errors[] = "Item {$item->item_id}: Quantity cannot be negative";
            }

            // Check for negative prices
            if ($item->changed_unit_price !== null && $item->changed_unit_price < 0) {
                $errors[] = "Item {$item->item_id}: Unit price cannot be negative";
            }

            // Check if item is being cancelled and modified at the same time
            if ($item->cancel_item && ($item->changed_qty !== null || $item->changed_unit_price !== null)) {
                $warnings[] = "Item {$item->item_id}: Item is cancelled, other changes will be ignored";
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
