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

    public function getWorkflowAttribute()
    {
        return collect([
            PurchaseOrderProcessingStatus::PENDING->value => [
                PurchaseOrderProcessingStatus::PREPAYMENT->value,
                PurchaseOrderProcessingStatus::ISSUED->value,
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
                PurchaseOrderProcessingStatus::POSTPAYMENT->value,
            ],
            PurchaseOrderProcessingStatus::POSTPAYMENT->value => [
                PurchaseOrderProcessingStatus::SERVED->value,
            ],
            PurchaseOrderProcessingStatus::SERVED->value => [],
        ])->toArray();
    }

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

    public function getCanTransitionToAttribute(): bool
    {
        $workflow = $this->workflow;
        $validNextStatuses = $workflow[$this->processing_status->value] ?? [];
        if ($this->isPrepayment && in_array($this->processing_status->value, [PurchaseOrderProcessingStatus::TURNED_OVER->value, PurchaseOrderProcessingStatus::POSTPAYMENT->value])) {
            $validNextStatuses[] = PurchaseOrderProcessingStatus::SERVED->value;
        }
        $newStatus = request()->input('processing_status', $this->processing_status->value);
        return in_array($newStatus, $validNextStatuses, true);
    }

    /**
     * ==================================================
     * MODEL HELPERS
     * ==================================================
     */
    public function getNextStatus(): ?PurchaseOrderProcessingStatus
    {
        $workflow = $this->workflow;
        $nextStatuses = $workflow[$this->processing_status->value] ?? [];
        return !empty($nextStatuses) ? PurchaseOrderProcessingStatus::from($nextStatuses[0]) : null;
    }

    public function getValidNextStatuses(): array
    {
        $workflow = $this->workflow;
        $validNextStatuses = $workflow[$this->processing_status->value] ?? [];
        if ($this->isPrepayment && in_array($this->processing_status->value, [PurchaseOrderProcessingStatus::TURNED_OVER->value, PurchaseOrderProcessingStatus::POSTPAYMENT->value])) {
            $validNextStatuses[] = PurchaseOrderProcessingStatus::SERVED->value;
        }
        return array_unique($validNextStatuses);
    }

    public function isServed(): bool
    {
        return $this->processing_status === PurchaseOrderProcessingStatus::SERVED;
    }
}
