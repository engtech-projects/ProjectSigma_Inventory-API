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
    public function group()
    {
        return $this->belongsTo(UOMGroup::class, 'group_id');
    }

    /**
    * ==================================================
    * LOCAL SCOPES
    * ==================================================
    */

    /**
    * ==================================================
    * DYNAMIC SCOPES
    * ==================================================
    */
}
