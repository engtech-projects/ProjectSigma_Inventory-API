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
        'item_code',
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
    public function getThicknessAttribute()
    {
        $value = $this->thickness_val;
        $uom = $this->thicknessUom ? $this->thicknessUom->name : $this->thickness_uom;

        return [
            'full' => "{$value} {$uom}",
            'abbreviated' => "{$value} " . ($this->thicknessUom ? $this->thicknessUom->symbol : $this->thickness_uom)
        ];
    }
    public function getLengthAttribute()
    {
        $value = $this->length_val;
        $uom = $this->lengthUom ? $this->lengthUom->name : $this->length_uom;

        return [
            'full' => "{$value} {$uom}",
            'abbreviated' => "{$value} " . ($this->lengthUom ? $this->lengthUom->symbol : $this->length_uom)
        ];
    }
    public function getWidthAttribute()
    {
        $value = $this->width_val;
        $uom = $this->widthUom ? $this->widthUom->name : $this->width_uom;

        return [
            'full' => "{$value} {$uom}",
            'abbreviated' => "{$value} " . ($this->widthUom ? $this->widthUom->symbol : $this->width_uom)
        ];
    }
    public function getHeightAttribute()
    {
        $value = $this->height_val;
        $uom = $this->heightUom ? $this->heightUom->name : $this->height_uom;

        return [
            'full' => "{$value} {$uom}",
            'abbreviated' => "{$value} " . ($this->heightUom ? $this->heightUom->symbol : $this->height_uom)
        ];
    }
    public function getOutsideDiameterAttribute()
    {
        $value = $this->outside_diameter_val;
        $uom = $this->outsideDiameterUom ? $this->outsideDiameterUom->name : $this->outside_diameter_uom;

        return [
            'full' => "{$value} {$uom}",
            'abbreviated' => "{$value} " . ($this->outsideDiameterUom ? $this->outsideDiameterUom->symbol : $this->outside_diameter_uom)
        ];
    }
    public function getInsideDiameterAttribute()
    {
        $value = $this->inside_diameter_val;
        $uom = $this->insideDiameterUom ? $this->insideDiameterUom->name : $this->inside_diameter_uom;

        return [
            'full' => "{$value} {$uom}",
            'abbreviated' => "{$value} " . ($this->insideDiameterUom ? $this->insideDiameterUom->symbol : $this->inside_diameter_uom)
        ];
    }
    public function getVolumeAttribute()
    {
        $value = $this->volume_val;
        $uom = $this->volumeUom ? $this->volumeUom->name : $this->volume_uom;

        return [
            'full' => "{$value} {$uom}",
            'abbreviated' => "{$value} " . ($this->volumeUom ? $this->volumeUom->symbol : $this->volume_uom)
        ];
    }
    public function getUomFullNameAttribute()
    {
        $uom = $this->uomName ? $this->uomName->name : 'null';
        return $uom;
    }



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
    public function volumeUom(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'volume_uom');
    }

    public function outsideDiameterUom(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'outside_diameter_uom');
    }

    public function insideDiameterUom(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'inside_diameter_uom');
    }

    public function uomName(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'uom');
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
