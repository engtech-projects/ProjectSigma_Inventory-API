<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItemProfilingItems extends Model
{
    use HasFactory;
    protected $table = 'request_itemprofiling_items';

    protected $fillable = [
        'request_itemprofiling_id',
        'item_profile_id',
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
    public function requestItemprofiling()
    {
        return $this->belongsTo(RequestItemprofiling::class, 'request_itemprofiling_id');
    }

    public function itemProfile()
    {
        return $this->belongsTo(ItemProfile::class, 'item_profile_id');
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
