<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


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
        'grade',
        'color',
        'uom',
        'inventory_type',
        'active_status',
    ];

    public function thicknessUom()
    {
        return $this->belongsTo(UOM::class, 'thickness_uom');
    }
    public function lengthUom()
    {
        return $this->belongsTo(UOM::class, 'length_uom');
    }

    public function widthUom()
    {
        return $this->belongsTo(UOM::class, 'width_uom');
    }

    public function heightUom()
    {
        return $this->belongsTo(UOM::class, 'height_uom');
    }

    public function outsideDiameterUom()
    {
        return $this->belongsTo(UOM::class, 'outside_diameter_uom');
    }

    public function insideDiameterUom()
    {
        return $this->belongsTo(UOM::class, 'inside_diameter_uom');
    }

    public function uom()
    {
        return $this->belongsTo(UOM::class);
    }



}
