<?php

namespace App\Models;

use App\Http\Services\ItemProfileService;
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
        'thickness',
        'length',
        'width',
        'height',
        'outside_diameter',
        'inside_diameter',
        'angle',
        'size',
        'weight',
        'volts',
        'plates',
        'part_number',
        'volume',
        'volume_uom',
        'grade',
        'color',
        'specification',
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
        'request_status',
    ];
    protected $casts = [
        "approvals" => 'array'
    ];

    public $appends = [
        'item_summary',
        'created_time_human'
    ];

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getUomFullNameAttribute()
    {
        $uom = $this->uomName ? $this->uomName->name : 'null';
        return $uom;
    }

    public function getNameSummaryAttribute()
    {
        return implode(' ', array_values(array_filter([
            $this->item_description,
            $this->thickness,
            $this->length,
            $this->width,
            $this->height,
            $this->outside_diameter,
            $this->inside_diameter,
            $this->angle,
            $this->size,
            $this->weight,
            $this->volts,
            $this->plates,
            $this->part_number,
            $this->volume,
            $this->volume_uom,
            $this->grade,
            $this->color,
            $this->specification,
        ])));
    }

    public function getItemSummaryAttribute()
    {
        $itemProfileService = new ItemProfileService();
        $attributes = $itemProfileService->getItemSummary($this);
        return $attributes->implode(' ');
    }
    public function getCodeNameAttribute()
    {
        return '[' . $this->item_code . '] ' . $this->item_description;
    }

    public function getCreatedTimeHumanAttribute()
    {
        return optional($this->created_at)->format('F j, Y');
    }
    public function getConvertableUnitsAttribute()
    {
        return $this->uomName->group?->uoms;
    }
    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */

    public function uomName(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'uom');
    }

    public function requestItemprofilingItems(): HasMany
    {
        return $this->hasMany(RequestItemprofilingItems::class);
    }
    public function ncpoItems()
    {
        return $this->hasMany(RequestNcpoItems::class, 'item_id');
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
            'item_code',
            'item_description',
            'thickness',
            'length',
            'width',
            'height',
            'outside_diameter',
            'inside_diameter',
            'angle',
            'size',
            'weight',
            'volts',
            'plates',
            'part_number',
            'specification',
            'volume',
            'volume_uom',
            'grade',
            'color',
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
    public function scopeSimpleSearch(Builder $query, $key)
    {
        // TEMPORARY: Simple search across multiple fields
        $query->whereRaw("CONCAT_WS(' ', item_code, item_description, specification) LIKE ?", ["%{$key}%"]);
    }
}
