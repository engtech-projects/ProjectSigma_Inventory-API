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
    public function requestBom()
    {
        return $this->belongsTo(RequestBom::class, 'request_bom_id');
    }



    /**
    * ==================================================
    * DYNAMIC SCOPES
    * ==================================================
    */
}
