<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ItemProfile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'item_profile';
    protected $fillable = [
        'sku',
        'item_description',
        'thickness_val',
        'thickness_uom',
        'length_val',
        'length_uom',
        'width_val',
        'width_uom',
        'height_val',
        'height_uom',
        'outside_diameter_val',
        'outside_diameter_uom',
        'inside_diameter_val',
        'inside_diameter_uom',
        'specification',
        'volume_val',
        'volume_uom',
        'grade',
        'color',
        'uom',
        'uom_conversion_group_id',
        'uom_conversion_value',
        'inventory_type',
        'item_group',
        'sub_item_group',
        'active_status',
        'is_approved',
        'approvals',
        'created_by',
        'request_status'
    ];
    protected $casts = [
        "approvals" => 'array'
    ];


    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */


    /**
    * ==================================================
    * MODEL RELATIONSHIPS
    * ==================================================
    */
    public function thicknessUom(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'thickness_uom');
    }
    public function lengthUom(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'length_uom');
    }

    public function widthUom(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'width_uom');
    }

    public function heightUom(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'height_uom');
    }

    public function outsideDiameterUom(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'outside_diameter_uom');
    }

    public function insideDiameterUom(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'inside_diameter_uom');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UOM::class);
    }

    public function requestItemprofilingItems(): HasMany
    {
        return $this->hasMany(RequestItemprofilingItems::class);
    }

    /**
    * ==================================================
    * LOCAL SCOPES
    * ==================================================
    */
    public function scopeIsApproved(Builder $query): void
    {
        $query->where('is_approved', true);
    }


    /**
    * ==================================================
    * DYNAMIC SCOPES
    * ==================================================
    */

}
