<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceQuotationItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'price_quotation_id',
        'item_id',
        'actual_brand',
        'unit_price',
        'remarks_during_canvass',
        'metadata',
    ];
    protected $casts = [
        'metadata' => 'array',
        'unit_price' => 'decimal:2',
    ];

    public function priceQuotation()
    {
        return $this->belongsTo(PriceQuotation::class);
    }
    public function item()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id');
    }

    public function getRequestStockItemAttribute()
    {
        return $this->priceQuotation?->requestProcurement?->requisitionSlip?->items?->firstWhere('item_id', $this->item_id);
    }

    public function getTotalAmountAttribute()
    {
        $unit = (float) ($this->unit_price ?? 0);
        $qty = (float) ($this->requestStockItem?->quantity ?? 0);
        return round($unit * $qty, 2);
    }
    public function getUnitPriceAttribute($value)
    {
        return $value ?? 0;
    }

    public function getQuantityAttribute($value)
    {
        return $value ?? 0;
    }
    public function getIsQuotedAttribute()
    {
        return $this->unit_price > 0;
    }
}
