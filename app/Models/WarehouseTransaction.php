<?php

namespace App\Models;

use App\Enums\RequestApprovalStatus;
use App\Enums\TransactionTypes;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;

class WarehouseTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;
    use HasApproval;

    protected $table = 'warehouse_transactions';

    protected $fillable = [
        'warehouse_id',
        'transaction_type',
        'charging_id',
        'charging_type',
        'approvals',
        'created_by',
        'request_status',
    ];

    protected $casts = [
        'approvals' => 'array',
        'transaction_type' => TransactionTypes::class,
    ];



    /**
    * ==================================================
    * MODEL ATTRIBUTES
    * ==================================================
    */
    public function scopeRequestStatusPending(Builder $query): void
    {
        $query->where('request_status', RequestApprovalStatus::PENDING);
    }

    public function scopeAuthUserPending(Builder $query): void
    {
        // Assuming authUserPending logic
        $query->where('created_by', auth()->user()->id);
    }

    public function completeRequestStatus()
    {
        $this->request_status = RequestApprovalStatus::APPROVED;
        $this->save();
        $this->refresh();
    }


    /**
    * ==================================================
    * MODEL RELATIONSHIPS
    * ==================================================
    */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function items()
    {
        return $this->hasMany(WarehouseTransactionItem::class);
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
