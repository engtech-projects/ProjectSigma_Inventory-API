<?php

namespace App\Models;

use App\Traits\HasReferenceNumber;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsolidatedRequest extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;
    use HasReferenceNumber;

    protected $fillable = [
        'reference_no',
        'purpose',
        'consolidated_by',
        'date_consolidated',
        'status',
        'remarks',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'date_consolidated' => 'date',
    ];

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */

    public function items()
    {
        return $this->hasMany(ConsolidatedRequestItems::class, 'consolidated_request_id');
    }
    public function slips()
    {
        return $this->hasManyThrough(
            RequestRequisitionSlip::class,
            ConsolidatedRequestItems::class,
            'consolidated_request_id',
            'id',
            'id',
            'requisition_slip_id'
        );
    }
    /**
     * ==================================================
     * ACCESSORS & SCOPES
     * ==================================================
     */
    public function getDetailedItemsAttribute()
    {
        $this->loadMissing('items.requisitionSlipItem.itemProfile');

        return $this->items->map(function ($item) {
            $requisitionItem = $item->requisitionSlipItem;
            $itemProfile = $requisitionItem?->itemProfile;
            return [
                'id' => $itemProfile?->id,
                'item_description' => $itemProfile?->item_description,
                'specification' => $requisitionItem?->specification,
                'preferred_brand' => $requisitionItem?->preferred_brand,
                'quantity' => $item->quantity_consolidated,
                'uom' => $requisitionItem?->uom_name,
                // to be used in getting the number of project departments requested
                // 'noOfProjectDepartmentsRequested' => $item->noOfProjectDepartments,
            ];
        });
    }
}
