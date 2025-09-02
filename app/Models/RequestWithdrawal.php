<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasApproval;
use App\Enums\OwnerType;

class RequestWithdrawal extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasApproval;

    protected $fillable = [
        'date_time',
        'warehouse_id',
        'chargeable_id',
        'chargeable_type',
        'equipment_no',
        'smr',
        'fuel',
        'reference_no',
        'metadata',
        'approvals',
        'request_status',
        'created_by',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'metadata' => 'array',
        'approvals' => 'array',
    ];

    /**
    * ==================================================
    * MODEL RELATIONS
    * ==================================================
    */
    public function warehouse()
    {
        return $this->belongsTo(SetupWarehouses::class, 'warehouse_id');
    }

    public function chargeable()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(RequestWithdrawalItem::class, 'request_withdrawal_id');
    }

    /**
     * Accessors to get the unified chargeable name
     */
    public function getChargeableNameAttribute()
    {
        if (!$this->chargeable) {
            return null;
        }

        switch ($this->chargeable_type) {
            case OwnerType::PROJECT->value:
                return $this->chargeable->project_code ?? null;
            case OwnerType::DEPARTMENT->value:
                return $this->chargeable->department_name ?? null;
            default:
                return null;
        }
    }
}
