<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialsReceivingItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'materials_receiving_items';
    protected $fillable = [
        'materials_receiving_id',
        'item_code',
        'item_profile_id',
        'specification',
        'actual_brand',
        'qty',
        'accepted_qty',
        'uom_id',
        'unit_price',
        'ext_price',
        'status',
        'remarks',
    ];
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
    * ==================================================
    * MODEL ATTRIBUTES
    * ==================================================
    */
    public function getUomNameAttribute()
    {
        return UOM::find($this->uom_id)?->name;
    }
    public function getItemProfileDataAttribute()
    {
        $itemProfile = ItemProfile::find($this->item_profile_id);

        return [
            'item_description' => $itemProfile?->item_description,
            'item_code' => $itemProfile?->item_code,
            'specification' => $itemProfile?->specification,
        ];
    }



    /**
    * ==================================================
    * MODEL RELATIONSHIPS
    * ==================================================
    */
    public function materialsReceiving()
    {
        return $this->belongsTo(MaterialsReceiving::class);
    }

}
