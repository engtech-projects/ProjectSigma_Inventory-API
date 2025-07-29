<?php

namespace App\Models;

use App\Enums\RequestStatuses;
use App\Enums\TransactionTypes;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class WarehouseTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;
    use HasApproval;

    protected $table = 'warehouse_transactions';

    protected $fillable = [
        'reference_no',
        'warehouse_id',
        'transaction_date',
        'metadata',
        'transaction_type',
        'charging_type',
        'charging_id',
        'approvals',
        'created_by',
        'request_status',
    ];

    protected $casts = [
        'approvals' => 'array',
        'transaction_type' => TransactionTypes::class,
        'metadata' => 'array',
    ];



    /**
    * ==================================================
    * MODEL ATTRIBUTES
    * ==================================================
    */
    public function scopeRequestStatusPending(Builder $query): void
    {
        $query->where('request_status', RequestStatuses::PENDING);
    }

    public function scopeBetweenDates(Builder $query, $dateFrom, $dateTo): void
    {
        $query->whereBetween('transaction_date', [$dateFrom, $dateTo]);
    }

    public function scopeAuthUserPending(Builder $query): void
    {
        // Assuming authUserPending logic
        $query->where('created_by', auth()->user()->id);
    }
    public function completeRequestStatus()
    {
        $this->request_status = RequestStatuses::APPROVED;
        $this->save();
        $this->refresh();
    }

    public function getWarehouseNameAttribute()
    {
        return $this->warehouse->name ?? 'Unknown Warehouse';
    }
    public function getProjectCodeAttribute(): ?string
    {
        $requestStock = RequestStock::find($this->charging_id, ['id', 'reference_no', 'section_id', 'section_type']);

        if (!$requestStock || !$requestStock->section_type || !$requestStock->section_id) {
            return null;
        }

        switch ($requestStock->section_type) {
            case 'Project':
                $project = Project::find($requestStock->section_id, ['project_code']);
                return $project?->project_code;

            case 'Department':
                $department = Department::find($requestStock->section_id, ['department_name']);
                return $department?->department_name;

            default:
                return null;
        }
    }
    public function getRSReferenceNoAttribute(): ?string
    {
        return RequestStock::where('id', $this->charging_id)->value('reference_no');
    }
    public function getSupplierCompanyNameAttribute(): string
    {
        return $this->metadata['supplier_id']
            ? RequestSupplier::where('id', $this->metadata['supplier_id'])->value('company_name')
            : 'Unknown Company';
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
        return $this->hasMany(WarehouseTransactionItem::class, 'warehouse_transaction_id');
    }
    public function transactions()
    {
        return $this->hasManyThrough(WarehouseTransactionItem::class, WarehouseTransaction::class);
    }

    public function requestStock()
    {
        return $this->belongsTo(RequestStock::class, 'charging_id')
            ->where('charging_type', RequestStock::class);
    }
    public function supplier()
    {
        return $this->belongsTo(RequestSupplier::class, 'supplier_id');
    }


    /**
    * ==================================================
    * LOCAL SCOPES
    * ==================================================
    */
    public function scopePettyCashMRR($query)
    {
        return $query->where('transaction_type', TransactionTypes::RECEIVING)
            ->whereJsonContains('metadata->is_petty_cash', true);
    }


    /**
    * ==================================================
    * DYNAMIC SCOPES
    * ==================================================
    */
    public function getTotalNetVatAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity_received * $item->unit_price;
        });
    }

    public function getTotalInputVatAttribute()
    {
        return $this->total_net_vat * 0.12; // 12% VAT
    }


    public function getGrandTotalAttribute()
    {
        return $this->items->sum('ext_price');
    }

    public function getTransactionDateHumanAttribute()
    {
        return $this->transaction_date ? Carbon::parse($this->transaction_date)->format('F j, Y') : null;
    }

    public function getServeStatusAttribute(): string
    {
        return $this->metadata['serve_status'] ?? 'Unserved';
    }



}
