<?php

namespace App\Models;

use App\Traits\HasApproval;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestPurchaseOrder extends Model
{
    use HasFactory;
    use ModelHelpers;
    use SoftDeletes;
    use HasApproval;

    protected $fillable = [
        'transaction_date',
        'po_number',
        'request_canvass_summary_id',
        'name_on_receipt',
        'delivered_to',
        'processing_status',
        'metadata',
        'created_by',
        'request_status',
        'approvals',
    ];
    protected $casts = [
        'transaction_date' => 'date',
        'metadata' => 'json',
        'approvals' => 'json',
        'processing_status' => 'string',
    ];
    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function requestCanvassSummary()
    {
        return $this->belongsTo(RequestCanvassSummary::class, 'request_canvass_summary_id');
    }
}
