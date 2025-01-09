<?php

namespace App\Models;

use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class RequestStock extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasApproval;

    protected $table = 'request_stocks';

    protected $fillable = [
        'request_for',
        'requestor',
        'requestor_address',
        'delivered_to',
        'warehouse_id',
        'request_no',
        'equipment_no',
        'date_prepared',
        'date_needed',
        'created_by',
        'approvals',
        'request_status'
    ];

    protected $casts = [
        'approvals' => 'array',
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
    public function items()
    {
        return $this->hasMany(RequestStockItem::class);
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
