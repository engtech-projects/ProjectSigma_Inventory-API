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
        return $this->hasMany(RequestCanvassSummaryItems::class);
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
        app(PurchaseOrderService::class)->createPurchaseOrderFromCanvass($this);
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
        if (!$this->relationLoaded('priceQuotation') || !$this->priceQuotation->relationLoaded('requestProcurement')) {
            return collect([]);
        }
        $procurement = $this->priceQuotation->requestProcurement;
        $quotations = $procurement->priceQuotations()->with([
            'supplier',
            'items' => fn ($q) => $q->orderBy('id')
        ])->latest()->take(3)->get();
        $procurement->loadMissing('requisitionSlip.items.itemProfile');
        $reqItems = $procurement->requisitionSlip->items->keyBy('item_id');
        $quotations->each(function ($q) use ($reqItems) {
            $q->items = $reqItems->map(function ($ri) use ($q) {
                return $q->items->keyBy('item_id')->get($ri->item_id, new PriceQuotationItem([
                    'item_id' => $ri->item_id,
                    'unit_price' => null,
                ]));
            })->values();
        });
        return $quotations->filter(function ($q) {
            return $q->supplier;
        });
    }
}
