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
        'price_quotation_item_id',
        'unit_price',
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
}
