<?php

namespace App\Models;

use App\Traits\HasApproval;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestNCPO extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;
    use HasApproval;

    protected $fillable = [
        'date',
        'ncpo_no',
        'po_id',
        'justification',
        'created_by',
        'approvals',
    ];
    protected $casts = [
        'date' => 'date',
        'approvals' => 'array',
        'metadata' => 'array',
    ];

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(RequestPurchaseOrder::class, 'po_id');
    }

    public function items()
    {
        return $this->hasMany(RequestNcpoItems::class);
    }

}
