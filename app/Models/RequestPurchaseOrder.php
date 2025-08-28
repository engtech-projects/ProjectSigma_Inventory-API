<?php

namespace App\Models;

use App\Enums\PurchaseOrderProcessingStatus;
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
        'processing_status' => PurchaseOrderProcessingStatus::class,
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

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */

    public function getIsPrepaymentAttribute(): bool
    {
        return $this->requestCanvassSummary
            ? strtolower($this->requestCanvassSummary->terms_of_payment) === 'prepayment in full'
            : false;
    }

    public function getProcessingFlowAttribute()
    {
        return [
            PurchaseOrderProcessingStatus::PENDING->value => [
                $this->is_prepayment
                    ? PurchaseOrderProcessingStatus::PREPAYMENT->value
                    : PurchaseOrderProcessingStatus::ISSUED->value,
            ],
            PurchaseOrderProcessingStatus::PREPAYMENT->value => [
                PurchaseOrderProcessingStatus::ISSUED->value,
            ],
            PurchaseOrderProcessingStatus::ISSUED->value => [
                PurchaseOrderProcessingStatus::ITEMS_RECEIVED->value,
            ],
            PurchaseOrderProcessingStatus::ITEMS_RECEIVED->value => [
                PurchaseOrderProcessingStatus::CHANGES->value,
                PurchaseOrderProcessingStatus::TURNED_OVER->value,
            ],
            PurchaseOrderProcessingStatus::CHANGES->value => [
                PurchaseOrderProcessingStatus::TURNED_OVER->value,
            ],
            PurchaseOrderProcessingStatus::TURNED_OVER->value => [
                $this->is_prepayment
                    ? PurchaseOrderProcessingStatus::SERVED->value
                    : PurchaseOrderProcessingStatus::POSTPAYMENT->value,
            ],
            PurchaseOrderProcessingStatus::POSTPAYMENT->value => [
                PurchaseOrderProcessingStatus::SERVED->value,
            ],
            PurchaseOrderProcessingStatus::SERVED->value => [],
        ];
    }

    public function getAllowedNextStatusesAttribute(): array
    {
        $workflow = $this->processing_flow;
        return $workflow[$this->processing_status->value] ?? [];
    }

    /**
     * ==================================================
     * MODEL HELPERS
     * ==================================================
     */

    public function getIsServedAttribute(): bool
    {
        return $this->processing_status === PurchaseOrderProcessingStatus::SERVED;
    }
}
