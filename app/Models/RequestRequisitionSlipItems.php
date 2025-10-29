<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestRequisitionSlipItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_requisition_slip_id',
        'quantity',
        'unit',
        'item_id',
        'specification',
        'preferred_brand',
        'reason',
        'location',
        'location_qty',
        'is_approved',
    ];

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    protected $appends = ['item_description', 'uom_name'];

    public function getUomNameAttribute()
    {
        return UOM::find($this->unit)?->name;
    }
    public function getItemDescriptionAttribute()
    {
        return $this->itemProfile ? $this->itemProfile->item_description : null;
    }

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function uom()
    {
        return $this->belongsTo(UOM::class);
    }
    public function requisitionSlip()
    {
        return $this->belongsTo(RequestRequisitionSlip::class, 'request_requisition_slip_id', 'id');
    }
    public function itemProfile()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id', 'id');
    }

    public function section()
    {
        return $this->morphTo();
    }
    public function requisitionSlipItem()
    {
        return $this->hasOneThrough(
            RequestRequisitionSlipItems::class,
            PriceQuotationItem::class,
            'id',                // Foreign key on price_quotation_items
            'item_id',           // Foreign key on requisition_slip_items
            'price_quotation_item_id', // Local key on canvass_summary_items
            'item_id'            // Local key on price_quotation_items
        );
    }
    public function getProcessingDetailsAttribute()
    {
        $requestProcurement = $this->requisitionSlip->requestProcurement;
        $details = [];
        $pettyCash = TransactionMaterialReceiving::where('metadata->rs_id', $this->requisitionSlip->id)
        ->where('metadata->is_petty_cash', true)
        ->with(['items' => fn ($query) => $query->where('item_id', $this->item_id)])
        ->get();
        $pettyCash = $pettyCash->filter(fn ($mrr) => $mrr->items->where('item_id', $this->item_id)->isNotEmpty());

        if (!$pettyCash->isEmpty()) {
            $details['petty_cash'] = $pettyCash->flatMap(fn ($mrr) => $mrr->items->where('item_id', $this->item_id)->map(fn ($item) => [
                        'acceptance_status' => $item->acceptance_status,
                        'serve_status' => $item->serve_status,
                        'remarks' => $item->remarks,
                    ]))->values()->toArray();
        }
        if (!$requestProcurement) {
            return !empty($details) ? $details : null;
        }
        $requestProcurement->loadMissing([
            'priceQuotations.supplier',
            'priceQuotations.items' => fn ($query) => $query->where('item_id', $this->item_id),
            'priceQuotations.canvassSummaries.purchaseOrder.ncpos',
        ]);
        $relatedPQs = $requestProcurement->priceQuotations
            ->filter(fn ($pq) => $pq->items->isNotEmpty());
        $details['price_quotations_count'] = $relatedPQs->count();
        if ($relatedPQs->isEmpty()) {
            return $details;
        }
        $canvassSummaries = $relatedPQs
            ->flatMap(fn ($pq) => $pq->canvassSummaries->map(fn ($cs) => [
                'id' => $cs->id,
                'suppliers' => $pq->supplier?->company_name,
                'status' => $cs->request_status,
            ]))
            ->values();
        if ($canvassSummaries->isNotEmpty()) {
            $details['canvass_summaries'] = $canvassSummaries;
        }
        $purchaseOrders = $relatedPQs
            ->flatMap(fn ($pq) => $pq->canvassSummaries->pluck('purchaseOrder'))
            ->filter()
            ->unique('id')
            ->sortByDesc('created_at')
            ->map(fn ($po) => [
                'id' => $po->id,
                'request_status' => $po->request_status,
                'processing_status' => $po->processing_status,
            ])
            ->values();
        if ($purchaseOrders->isNotEmpty()) {
            $details['purchase_orders'] = $purchaseOrders;
        }
        $ncpos = $relatedPQs
            ->flatMap(fn ($pq) => $pq->canvassSummaries)
            ->pluck('purchaseOrder')
            ->filter()
            ->flatMap(fn ($po) => $po->ncpos ?? collect())
            ->map(fn ($ncpo) => [
                'id' => $ncpo->id,
                'request_status' => $ncpo->request_status,
                'justification' => $ncpo->justification,
                'new_po_total' => $ncpo->new_po_total,
                'original_total' => $ncpo->original_total,
            ])
            ->values();
        if ($ncpos->isNotEmpty()) {
            $details['ncpos'] = $ncpos;
        }
        return $details;
    }
}
