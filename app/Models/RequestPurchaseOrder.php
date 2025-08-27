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

    protected static function getWorkflow(): array
    {
        return [
            PurchaseOrderProcessingStatus::PENDING->value => [
                PurchaseOrderProcessingStatus::PREPAYMENT->value,
                PurchaseOrderProcessingStatus::ISSUED->value
            ],
            PurchaseOrderProcessingStatus::PREPAYMENT->value => [
                PurchaseOrderProcessingStatus::ISSUED->value
            ],
            PurchaseOrderProcessingStatus::ISSUED->value => [
                PurchaseOrderProcessingStatus::ITEMS_RECEIVED->value
            ],
            PurchaseOrderProcessingStatus::ITEMS_RECEIVED->value => [
                PurchaseOrderProcessingStatus::CHANGES->value,
                PurchaseOrderProcessingStatus::TURNED_OVER->value
            ],
            PurchaseOrderProcessingStatus::CHANGES->value => [
                PurchaseOrderProcessingStatus::TURNED_OVER->value
            ],
            PurchaseOrderProcessingStatus::TURNED_OVER->value => [
                PurchaseOrderProcessingStatus::POSTPAYMENT->value
            ],
            PurchaseOrderProcessingStatus::POSTPAYMENT->value => [
                PurchaseOrderProcessingStatus::SERVED->value
            ],
            PurchaseOrderProcessingStatus::SERVED->value => [],
        ];
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
     * MODEL HELPERS
     * ==================================================
     */
    public function getNextStatus(): ?PurchaseOrderProcessingStatus
    {
        $workflow = self::getWorkflow();
        $nextStatuses = $workflow[$this->processing_status->value] ?? [];
        return !empty($nextStatuses) ? PurchaseOrderProcessingStatus::from($nextStatuses[0]) : null;
    }

    public function canTransitionTo(PurchaseOrderProcessingStatus $newStatus): bool
    {
        $workflow = self::getWorkflow();
        $validNextStatuses = $workflow[$this->processing_status->value] ?? [];
        if ($newStatus === PurchaseOrderProcessingStatus::SERVED && $this->isPrepayment()) {
            $validNextStatuses[] = PurchaseOrderProcessingStatus::TURNED_OVER->value;
        }

        return in_array($newStatus->value, $validNextStatuses, true);
    }

    public function getValidNextStatuses(): array
    {
        $workflow = self::getWorkflow();
        return $workflow[$this->processing_status->value] ?? [];
    }

    public function isPrepayment(): bool
    {
        return $this->processing_status === PurchaseOrderProcessingStatus::PREPAYMENT;
    }

    public function isServed(): bool
    {
        return $this->processing_status === PurchaseOrderProcessingStatus::SERVED;
    }
}
