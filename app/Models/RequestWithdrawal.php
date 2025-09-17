<?php

namespace App\Models;

use App\Enums\RequestStatuses;
use App\Http\Services\WithdrawalService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasApproval;

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

    public function warehouseStockTransactions()
    {
        return $this->morphMany(WarehouseStockTransactions::class, 'referenceable');
    }

    /**
     * Accessors
     */
    public function getChargeableNameAttribute()
    {
        return $this->chargeable?->department_name ?? $this->chargeable?->project_code ?? null;
    }

    /**
     * ==================================================
     * MODEL FUNCTIONS
     * ==================================================
     */
    public function completeRequestStatus()
    {
        $this->request_status = RequestStatuses::APPROVED->value;
        $withdrawalService = new WithdrawalService($this);
        $withdrawalService->withdrawItemsFromWarehouse($this->items);
        $this->save();
        $this->refresh();
    }
}
