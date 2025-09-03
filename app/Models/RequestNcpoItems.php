<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ModelHelpers;

class RequestNcpoItems extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;

    protected $fillable = [
        'request_ncpo_id',
        'item_id',
        'changed_supplier_id',
        'changed_item_description',
        'changed_specification',
        'changed_qty',
        'changed_uom_id',
        'changed_unit_price',
        'changed_brand',
        'cancel_item',
        'metadata'
    ];

    protected $casts = [
        'cancel_item' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function requestNcpo()
    {
        return $this->belongsTo(RequestNCPO::class, 'request_ncpo_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id');
    }

    public function supplier()
    {
        return $this->belongsTo(RequestSupplier::class, 'changed_supplier_id');
    }
    /**
     * ==================================================
     * MODEL METHODS
     * ==================================================
     */
    public function requisitionSlipItem()
    {
        return $this->requestNcpo
            ->purchaseOrder
            ->requestCanvassSummary
            ->priceQuotation
            ->requestProcurement
            ->requisitionSlip
            ->items()
            ->where('item_id', $this->item_id)
            ->first();
    }

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getCanvassSummaryItemAttribute()
    {
        $canvassSummaryId = $this->requestNcpo?->purchaseOrder?->request_canvass_summary_id;

        if (!$canvassSummaryId) {
            return null;
        }

        return RequestCanvassSummaryItems::where('request_canvass_summary_id', $canvassSummaryId)
            ->where('item_id', $this->item_id)
            ->first();
    }
    public function getOriginalQuantityAttribute()
    {
        return $this->requisitionSlipItem()?->quantity ?? 0;
    }

    public function getOriginalUnitPriceAttribute()
    {
        return $this->canvass_summary_item?->unit_price ?? 0;
    }

    public function getOriginalTotalAttribute()
    {
        return $this->original_quantity * $this->original_unit_price;
    }

    public function getNewTotalAttribute()
    {
        if ($this->cancel_item) {
            return 0;
        }
        $originalQty = $this->original_quantity;
        $originalPrice = $this->original_unit_price;
        $qty = $this->changed_qty ?? $originalQty;
        $unitPrice = $this->changed_unit_price ?? $originalPrice;
        return $qty * $unitPrice;
    }
}
