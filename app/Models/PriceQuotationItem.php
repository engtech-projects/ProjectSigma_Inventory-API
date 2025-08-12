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
    ];

    public function priceQuotation()
    {
        return $this->belongsTo(PriceQuotation::class);
    }

    public function getRequestStockItemAttribute()
    {
        return $this->priceQuotation?->requestProcurement?->requisitionSlip?->items?->firstWhere('item_id', $this->item_id);
    }

    public function getTotalAmountAttribute()
    {
        $qty = $this->requestStockItem?->quantity ?? 0;
        return $this->unit_price * $qty;
    }
}
