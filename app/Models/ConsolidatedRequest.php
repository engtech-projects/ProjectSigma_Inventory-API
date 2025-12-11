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
    public function getDetailedItemsAttribute()
    {
        $this->loadMissing([
            'items.requisitionSlipItem.itemProfile',
            'items.requisitionSlip',
        ]);
        $grouped = $this->items->groupBy(function ($consItem) {
            $rsi = $consItem->requisitionSlipItem;
            return $rsi->item_id
                . '|' . ($rsi->specification ?? '')
                . '|' . ($rsi->preferred_brand ?? '');
        });
        return $grouped->map(function ($group) {
            $first = $group->first();
            $requisitionItem = $first->requisitionSlipItem;
            $itemProfile = $requisitionItem?->itemProfile;
            $totalQuantity = $group->sum('quantity_consolidated');
            $uniqueDepartmentsCount = $group->pluck('requisition_slip_id')->unique()->count();
            $sourceRsRefs = $group->pluck('requisitionSlip.reference_no')->unique()->values();
            return [
                'id'                          => $itemProfile?->id,
                'item_description'            => $itemProfile?->item_description,
                'specification'               => $requisitionItem?->specification,
                'preferred_brand'             => $requisitionItem?->preferred_brand,
                'quantity'                    => $totalQuantity,
                'uom'                         => $requisitionItem?->uom_name,
                'no_of_project_departments_requested' => $uniqueDepartmentsCount,
                'source_requisition_slips'    => $sourceRsRefs->toArray(),
            ];
        })->values();
    }
}
