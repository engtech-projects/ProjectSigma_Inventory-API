<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestCanvassSummaryItems extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'request_canvass_summary_id',
        'item_id',
        'metadata',
        'unit_price',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function priceQuotationItem()
    {
        return $this->belongsTo(PriceQuotationItem::class);
    }

    public function requestCanvassSummary()
    {
        return $this->belongsTo(RequestCanvassSummary::class);
    }

    public function itemProfile()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id');
    }
    public function requisitionSlipItem()
    {
        return $this->belongsTo(RequestRequisitionSlipItems::class);
    }

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */

    public function getItemNameAttribute()
    {
        return $this->itemProfile?->item_name;
    }

    public function getItemCodeAttribute()
    {
        return $this->itemProfile?->item_code;
    }

    public function getRequisitionSlipItemAttribute()
    {
        return $this->requestCanvassSummary
            ->priceQuotation
            ->requestProcurement
            ->requisitionSlip
            ->items
            ->firstWhere('item_id', $this->item_id);
    }

    public function getQuantityAttribute()
    {
        return $this->requisitionSlipItem?->quantity;
    }

    public function getTotalAmountAttribute(): float
    {
        $quantity = $this->quantity ?? $this->requisitionSlipItem?->quantity ?? 0;
        $unitPrice = $this->unit_price ?? 0;
        return $unitPrice * $quantity;
    }
}
