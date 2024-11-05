<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Details extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'details';
    protected $fillable = [
        'request_bom_id',
        'item_id',
        'uom_id',
        'unit_price',
        'quantity',
    ];

    public $appends = [
        'unit'
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

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function requestBom()
    {
        return $this->belongsTo(RequestBom::class, 'request_bom_id');
    }
    public function uom()
    {
        return $this->belongsTo(UOM::class, 'uom_id');
    }
    public function items()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id');
    }


    /**
     * ==================================================
     * DYNAMIC SCOPES
     * ==================================================
     */
}
