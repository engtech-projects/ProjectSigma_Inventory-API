<?php

namespace App\Models;

use App\Enums\RequestStatuses;
use App\Http\Services\PurchaseOrderService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasApproval;
use App\Traits\ModelHelpers;

class RequestCanvassSummary extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasApproval;
    use ModelHelpers;
    protected $table = "request_canvass_summary";
    protected $fillable = [
        'price_quotation_id',
        'cs_number',
        'terms_of_payment',
        'availability',
        'delivery_terms',
        'remarks',
        'created_by',
        'metadata',
        'approvals',
        'request_status',
    ];
    protected $casts = [
        'metadata' => 'array',
        'approvals' => 'array',
    ];

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function priceQuotation()
    {
        return $this->belongsTo(PriceQuotation::class);
    }

    public function items()
    {
        return $this->hasMany(RequestCanvassSummaryItems::class)
        ->whereNotNull('unit_price');
    }
    public function purchaseOrder()
    {
        return $this->hasOne(RequestPurchaseOrder::class);
    }

    public function requestProcurement()
    {
        return $this->belongsTo(RequestProcurement::class);
    }

    /**
     * ==================================================
     * MODEL FUNCTIONS
     * ==================================================
     */
    public function completeRequestStatus()
    {
        $this->request_status = RequestStatuses::APPROVED;
        $this->save();
        $this->refresh();
        PurchaseOrderService::createPurchaseOrderFromCanvass($this);
    }

    public function getGrandTotalAmountAttribute(): float
    {
        return $this->items->sum(function ($item) {
            $quantity = $item->requisition_slip_item?->quantity ?? 0;
            $unitPrice = $item->unit_price ?? 0;
            return $unitPrice * $quantity;
        });
    }
    public function getOrderedSuppliersAttribute()
    {
        // Ensure relations are available
        if (!$this->relationLoaded('priceQuotation') || !$this->priceQuotation->relationLoaded('requestProcurement')) {
            return collect([]);
        }
        $procurement   = $this->priceQuotation->requestProcurement;
        $selectedId    = $this->priceQuotation->supplier_id;
        // Fetch latest 3 quotations with suppliers and items
        $quotations = $procurement->priceQuotations()
            ->with([
                'supplier',
                'items' => fn ($q) => $q->orderBy('id')
            ])
            ->latest()
            ->take(3)
            ->get();
        // Load requisition slip items and index by item_id
        $procurement->loadMissing('requisitionSlip.items.itemProfile');
        $reqItems = $procurement->requisitionSlip->items->keyBy('item_id');
        // Normalize each quotation so every requisition item has a placeholder if missing
        $quotations->each(function ($quotation) use ($reqItems) {
            $quotation->items = $reqItems->map(function ($reqItem) use ($quotation) {
                return $quotation->items->keyBy('item_id')->get(
                    $reqItem->item_id,
                    new PriceQuotationItem([
                        'item_id'    => $reqItem->item_id,
                        'unit_price' => null,
                    ])
                );
            })->values();
        });
        // Keep only quotations with valid suppliers
        $quotations = $quotations->filter(fn ($q) => $q->supplier);
        // Reorder: put the selected supplier first
        $quotations = $quotations->sortByDesc(fn ($q) => $q->supplier_id === $selectedId)->values();
        return $quotations;
    }

    public function getSelectedSupplierIdAttribute()
    {
        return $this->priceQuotation?->supplier_id;
    }
}
