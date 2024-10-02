<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'warehouse';

    protected $fillable = [
        'id',
        'name',
        'location',
        'owner_id',
        'owner_type',
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
