<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestProcurementCanvasser extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'request_procurement_canvassers';

    protected $fillable = [
        'user_id',
        'request_procurement_id',
    ];

    public function requestProcurement()
    {
        return $this->belongsTo(RequestProcurement::class);
    }

}
