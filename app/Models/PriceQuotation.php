<?php

namespace App\Models;

use App\Traits\HasReferenceNumber;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class PriceQuotation extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasReferenceNumber;
    use ModelHelpers;

    protected $fillable = [
        'request_procurement_id',
        'supplier_id',
        'quotation_no',
        'metadata',
    ];
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function items()
    {
        return $this->hasMany(PriceQuotationItem::class);
    }

    public function requestProcurement()
    {
        return $this->belongsTo(RequestProcurement::class);
    }

    public function supplier()
    {
        return $this->belongsTo(RequestSupplier::class, 'supplier_id');
    }
    public function canvassSummaries()
    {
        return $this->hasMany(RequestCanvassSummary::class);
    }

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getGrandTotalAmountAttribute()
    {
        return $this->items->filter(fn ($item) => $item->is_quoted)->sum('total_amount');
    }

    /**
     * ==================================================
     * MODEL SCOPES
     * ==================================================
     */

    /**
     * ==================================================
     * MODEL METHODS
     * ==================================================
     */
}
