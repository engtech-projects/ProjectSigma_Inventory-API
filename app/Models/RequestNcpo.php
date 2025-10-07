<?php

namespace App\Models;

use App\Enums\PurchaseOrderProcessingStatus;
use App\Enums\RequestStatuses;
use App\Http\Services\NcpoService;
use App\Traits\HasApproval;
use App\Traits\HasReferenceNumber;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestNcpo extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;
    use HasApproval;
    use HasReferenceNumber;
    protected $table = 'request_ncpos';
    protected $fillable = [
        'date',
        'ncpo_no',
        'po_id',
        'justification',
        'created_by',
        'approvals',
        'request_status',
        'metadata',
    ];
    protected $casts = [
        'date' => 'date',
        'approvals' => 'array',
        'metadata' => 'array',
    ];

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(RequestPurchaseOrder::class, 'po_id');
    }

    public function items()
    {
        return $this->hasMany(RequestNcpoItems::class, 'request_ncpo_id');
    }

    /**
     * ==================================================
     * MODEL ATTRIBUTE
     * ==================================================
     */
    public function getNewPoTotalAttribute()
    {
        return $this->items->sum(fn ($item) => $item->new_total);
    }
    public function getOriginalTotalAttribute()
    {
        return $this->purchaseOrder?->requestCanvassSummary?->grand_total_amount ?? 0;
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
        $this->purchaseOrder->processing_status = PurchaseOrderProcessingStatus::TURNED_OVER;
        $this->purchaseOrder->save();
        NcpoService::createMrrFromNcpo($this);
    }
}
