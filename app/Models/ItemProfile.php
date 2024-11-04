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
    public function getThicknessUomSymbolAttribute()
    {
        return $this->thicknessUom ? $this->thicknessUom->symbol : $this->thickness_uom;
    }

    public function getLengthUomSymbolAttribute()
    {
        return $this->lengthUom ? $this->lengthUom->symbol : $this->length_uom;
    }

    public function getWidthUomSymbolAttribute()
    {
        return $this->widthUom ? $this->widthUom->symbol : $this->width_uom;
    }

    public function getHeightUomSymbolAttribute()
    {
        return $this->heightUom ? $this->heightUom->symbol : $this->height_uom;
    }

    public function getVolumeUomSymbolAttribute()
    {
        return $this->volumeUom ? $this->volumeUom->symbol : $this->volume_uom;
    }
    public function getOutsideDiameterUomSymbolAttribute()
    {
        return $this->OutsideDiameterUom ? $this->OutsideDiameterUom->symbol : $this->outside_diameter_uom;
    }
    public function getInsideDiameterUomSymbolAttribute()
    {
        return $this->InsideDiameterUom ? $this->InsideDiameterUom->symbol : $this->inside_diameter_uom;
    }

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

    public function scopeSearch(Builder $query, $searchKey)
    {
        $fields = [
            'item_description', 'item_code', 'thickness_val', 'thickness_uom', 'length_val',
            'length_uom', 'width_val', 'width_uom', 'height_val', 'height_uom',
            'outside_diameter_val', 'outside_diameter_uom', 'inside_diameter_val',
            'inside_diameter_uom', 'volume_val', 'volume_uom', 'grade', 'color', 'specification'
        ];

        // Join UOM tables once for all *_uom fields
        $query->leftJoin('setup_uom as t_uom', 'item_profile.thickness_uom', '=', 't_uom.id')
            ->leftJoin('setup_uom as l_uom', 'item_profile.length_uom', '=', 'l_uom.id')
            ->leftJoin('setup_uom as w_uom', 'item_profile.width_uom', '=', 'w_uom.id')
            ->leftJoin('setup_uom as h_uom', 'item_profile.height_uom', '=', 'h_uom.id')
            ->leftJoin('setup_uom as od_uom', 'item_profile.outside_diameter_uom', '=', 'od_uom.id')
            ->leftJoin('setup_uom as id_uom', 'item_profile.inside_diameter_uom', '=', 'id_uom.id')
            ->leftJoin('setup_uom as v_uom', 'item_profile.volume_uom', '=', 'v_uom.id');

        // Split the search key for value and symbol matching
        $searchKeyParts = explode(' ', $searchKey);
        $hasTwoParts = count($searchKeyParts) === 2;

        return $query->where(function ($q) use ($fields, $searchKey, $searchKeyParts, $hasTwoParts) {
            // Simple text search for fields
            foreach ($fields as $field) {
                $q->orWhere($field, 'LIKE', "%{$searchKey}%");
            }

            // If search key is split into value and symbol parts, match each dimension
            if ($hasTwoParts) {
                list($valuePart, $symbolPart) = $searchKeyParts;
                $numericValue = is_numeric($valuePart) ? floatval($valuePart) : null;

                if ($numericValue) {
                    $q->orWhere(function ($q) use ($numericValue, $symbolPart) {
                        $q->whereRaw("CAST(thickness_val AS DECIMAL(10, 2)) = ?", [$numericValue])
                        ->where('t_uom.symbol', 'LIKE', "%{$symbolPart}%");
                    })->orWhere(function ($q) use ($numericValue, $symbolPart) {
                        $q->whereRaw("CAST(length_val AS DECIMAL(10, 2)) = ?", [$numericValue])
                        ->where('l_uom.symbol', 'LIKE', "%{$symbolPart}%");
                    })->orWhere(function ($q) use ($numericValue, $symbolPart) {
                        $q->whereRaw("CAST(width_val AS DECIMAL(10, 2)) = ?", [$numericValue])
                        ->where('w_uom.symbol', 'LIKE', "%{$symbolPart}%");
                    })->orWhere(function ($q) use ($numericValue, $symbolPart) {
                        $q->whereRaw("CAST(height_val AS DECIMAL(10, 2)) = ?", [$numericValue])
                        ->where('h_uom.symbol', 'LIKE', "%{$symbolPart}%");
                    })->orWhere(function ($q) use ($numericValue, $symbolPart) {
                        $q->whereRaw("CAST(outside_diameter_val AS DECIMAL(10, 2)) = ?", [$numericValue])
                        ->where('od_uom.symbol', 'LIKE', "%{$symbolPart}%");
                    })->orWhere(function ($q) use ($numericValue, $symbolPart) {
                        $q->whereRaw("CAST(inside_diameter_val AS DECIMAL(10, 2)) = ?", [$numericValue])
                        ->where('id_uom.symbol', 'LIKE', "%{$symbolPart}%");
                    })->orWhere(function ($q) use ($numericValue, $symbolPart) {
                        $q->whereRaw("CAST(volume_val AS DECIMAL(10, 2)) = ?", [$numericValue])
                        ->where('v_uom.symbol', 'LIKE', "%{$symbolPart}%");
                    });
                }
            }
        });
    }




    /**
     * ==================================================
     * DYNAMIC SCOPES
     * ==================================================
     */
}
