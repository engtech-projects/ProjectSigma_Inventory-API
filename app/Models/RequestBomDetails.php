<?php

namespace App\Models;

use App\Http\Services\ItemProfileService;
use App\Http\Traits\HasConversionUnit;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestBomDetails extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasConversionUnit;
    use ModelHelpers;

    protected $fillable = [
        'request_bom_id',
        'item_id',
        'uom_id',
        'unit_price',
        'quantity',
    ];
    public $appends = [
        'unit',
        'item_summary',
        'convertable_units',
    ];

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getUnitAttribute()
    {
        return UOM::where('group_id', $this->uom->group_id)->get();
    }
    public function getItemSummaryAttribute()
    {
        $itemProfileService = new ItemProfileService();
        $attributes = $itemProfileService->getItemSummary($this->items);
        return $attributes->implode(' ') ?? '';
    }

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function requestBom()
    {
        return $this->belongsTo(RequestBOM::class, 'request_bom_id');
    }
    public function uom()
    {
        return $this->belongsTo(UOM::class, 'uom_id')->withDefault();
    }
    public function items()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id');
    }
}
