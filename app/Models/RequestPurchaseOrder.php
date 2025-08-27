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
     * MODEL HELPERS
     * ==================================================
     */
    public function getNextStatus(): ?PurchaseOrderProcessingStatus
    {
        return match ($this->processing_status) {
            PurchaseOrderProcessingStatus::PENDING => PurchaseOrderProcessingStatus::PREPAYMENT,
            PurchaseOrderProcessingStatus::PREPAYMENT => PurchaseOrderProcessingStatus::ISSUED,
            PurchaseOrderProcessingStatus::ISSUED => PurchaseOrderProcessingStatus::ITEMS_RECEIVED,
            PurchaseOrderProcessingStatus::ITEMS_RECEIVED => PurchaseOrderProcessingStatus::CHANGES,
            PurchaseOrderProcessingStatus::CHANGES => PurchaseOrderProcessingStatus::TURNED_OVER,
            PurchaseOrderProcessingStatus::TURNED_OVER => PurchaseOrderProcessingStatus::POSTPAYMENT,
            PurchaseOrderProcessingStatus::POSTPAYMENT => PurchaseOrderProcessingStatus::SERVED,
            PurchaseOrderProcessingStatus::SERVED => null,
            default => null,
        };
    }

    public function canTransitionTo(PurchaseOrderProcessingStatus $newStatus): bool
    {
        $nextStatus = $this->getNextStatus();
        return $nextStatus === $newStatus;
    }
}
