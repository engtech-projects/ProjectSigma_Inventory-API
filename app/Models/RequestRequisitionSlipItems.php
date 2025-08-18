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

}
