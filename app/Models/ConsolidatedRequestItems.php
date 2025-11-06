<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsolidatedRequestItems extends Model
{
    use HasFactory;
    protected $fillable = [
        'consolidated_request_id',
        'requisition_slip_item_id',
        'requisition_slip_id',
        'quantity_consolidated',
        'status',
        'remarks',
    ];


    protected $casts = [
        'metadata' => 'array',
        'quantity_consolidated' => 'decimal:2'
    ];

    protected $appends = [
        'item_description',
        'uom_name',
        'source_requisition_slips'
    ];
    /**
      * ==================================================
      * MODEL RELATIONSHIPS
      * ==================================================
      */

    public function consolidatedRequest()
    {
        return $this->belongsTo(ConsolidatedRequest::class);
    }

    public function requisitionSlipItem()
    {
        return $this->belongsTo(RequestRequisitionSlipItems::class);
    }

    public function requisitionSlip()
    {
        return $this->belongsTo(RequestRequisitionSlip::class);
    }

    public function getItemDescriptionAttribute()
    {
        return $this->requisitionSlipItem->specification ?? null;
    }

    public function getUomNameAttribute()
    {
        return $this->requisitionSlipItem->unit ?? null;
    }

    public function getSourceRequisitionSlipAttribute()
    {
        return $this->requisitionSlip->reference_no ?? null;
    }

}
