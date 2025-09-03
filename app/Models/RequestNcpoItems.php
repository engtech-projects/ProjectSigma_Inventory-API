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
    public function canvassSummaryItem()
    {
        return $this->hasOne(RequestCanvassSummaryItems::class, 'item_id', 'item_id');
    }

    /**
     * ==================================================
     * MODEL ATTRIBUTE
     * ==================================================
     */
    public function getNewTotalAttribute()
    {
        if ($this->cancel_item) {
            return 0;
        }
        $originalQty = $this->canvassSummaryItem?->requisitionSlipItem?->quantity ?? 0;
        $originalPrice = $this->canvassSummaryItem?->unit_price ?? 0;
        $qty = $this->changed_qty ?? $originalQty;
        $unitPrice = $this->changed_unit_price ?? $originalPrice;
        return $qty * $unitPrice;
    }
}
