<?php

namespace App\Models;

use App\Enums\RequestStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasApproval;

class RequestCanvassSummary extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasApproval;

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
    }
}
