<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UOM extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'setup_uom';
    protected $fillable = [
        'id',
        'group_id',
        'name',
        'symbol',
        'conversion',
        'is_standard',
    ];

    public function group()
    {
        return $this->belongsTo(UOMGroup::class, 'group_id');
    }
    public function thicknessItemProfiles()
    {
        return $this->hasMany(ItemProfile::class, 'thickness_uom');
    }

    public function lengthItemProfiles()
    {
        return $this->hasMany(ItemProfile::class, 'length_uom');
    }

    public function widthItemProfiles()
    {
        return $this->hasMany(ItemProfile::class, 'width_uom');
    }

    public function heightItemProfiles()
    {
        return $this->hasMany(ItemProfile::class, 'height_uom');
    }

    public function outsideDiameterItemProfiles()
    {
        return $this->hasMany(ItemProfile::class, 'outside_diameter_uom');
    }

    public function insideDiameterItemProfiles()
    {
        return $this->hasMany(ItemProfile::class, 'inside_diameter_uom');
    }

}
