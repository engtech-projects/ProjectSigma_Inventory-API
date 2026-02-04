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
    public function mrr()
    {
        return $this->hasOne(TransactionMaterialReceiving::class, 'po_id', 'id');
    }
    public function ncpos()
    {
        return $this->hasMany(RequestNcpo::class, 'po_id');
    }
    public function supplier()
    {
        return $this->belongsTo(RequestSupplier::class, 'supplier_id');
    }
    public function mrrNcpoItems()
    {
        return TransactionMaterialReceivingItem::whereHas('transactionMaterialReceiving', function ($query) {
            $query->where('metadata->ncpo_id', $this->id);
        });
    }
    /**
     * ==================================================
     * MODEL scopes
     * ==================================================
     */
    public function scopeRequestRequisitionSlip($query)
    {
        return $query->whereHas('requestCanvassSummary.priceQuotation.requestProcurement', function ($q) {
            $q->whereHas('requisitionSlip');
        });
    }
    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getPriceQuotationAttribute()
    {
        return $this->requestCanvassSummary?->priceQuotation;
    }

    public function getRequisitionSlipAttribute()
    {
        return $this->priceQuotation?->requestProcurement?->requisitionSlip;
    }

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
    public function getIsServedAttribute(): bool
    {
        return $this->processing_status === PurchaseOrderProcessingStatus::SERVED;
    }
    public function getWarehouseIdAttribute()
    {
        return $this->requisitionSlip?->warehouse_id;
    }
    public function getSupplierIdAttribute()
    {
        return $this->priceQuotation?->supplier_id;
    }
    public function getTermsOfPaymentAttribute()
    {
        return $this->requestCanvassSummary?->terms_of_payment;
    }
    public function getRsIdAttribute()
    {
        return $this->requisitionSlip?->id;
    }
    public function getItemsAttribute()
    {
        $requisitionItems = $this->requisitionSlip?->items ?? collect();
        $csItems          = $this->requestCanvassSummary?->items ?? collect();

        return collect($this->metadata['items'] ?? [])->map(function ($item) use ($requisitionItems, $csItems) {
            $reqItem = $requisitionItems->firstWhere('item_id', $item['item_id']);
            $csItem  = $csItems->firstWhere('item_id', $item['item_id']);
            return (object) [
                'id'                   => $item['id'] ?? null,
                'item_id'              => $item['item_id'],
                'item_description'     => $item['item_description'],
                'specification'        => $item['specification'],
                'requested_quantity'   => $reqItem?->quantity,
                'quantity'             => round($item['quantity'], 2),
                'uom'                  => $item['uom'],
                'uom_id'               => $item['uom_id'],
                'actual_brand_purchase' => $item['actual_brand_purchase'],
                'unit_price'           => $item['unit_price'],
                'remarks'              => $csItem?->remarks,
                'net_amount'           => round($item['net_amount'], 2),
                'net_vat'              => round($item['net_vat'], 2),
                'input_vat'            => round($item['input_vat'], 2),
            ];
        });
    }
}
