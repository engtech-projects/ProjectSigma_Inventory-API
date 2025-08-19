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
}
