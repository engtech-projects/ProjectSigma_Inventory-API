<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestCanvassSummary extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'price_quotation_id',
        'metadata',
        'approvals',
        'request_status',
        'created_by',
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

}
