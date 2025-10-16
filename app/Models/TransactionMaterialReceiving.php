<?php

namespace App\Models;

use App\Enums\ReceivingAcceptanceStatus;
use App\Enums\ServeStatus;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionMaterialReceiving extends Model
{
    use HasFactory;
    use ModelHelpers;
    use SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'reference_no',
        'supplier_id',
        'reference',
        'terms_of_payment',
        'particulars',
        'transaction_date',
        'evaluated_by_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function warehouse()
    {
        return $this->belongsTo(SetupWarehouses::class);
    }
    public function supplier()
    {
        return $this->belongsTo(RequestSupplier::class);
    }
    public function requisitionSlip()
    {
        return $this->belongsTo(RequestRequisitionSlip::class);
    }
    public function evaluatedBy()
    {
        return $this->belongsTo(User::class, 'evaluated_by_id');
    }
    public function items()
    {
        return $this->hasMany(TransactionMaterialReceivingItem::class);
    }
    public function warehouseStockTransactions()
    {
        return $this->morphMany(WarehouseStockTransactions::class, 'referenceable');
    }
    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getIsPettyCashAttribute()
    {
        return $this->metadata['is_petty_cash'] ?? false;
    }
    public function getIsNcpoAttribute()
    {
        return $this->metadata['is_ncpo'] ?? false;
    }
    public function getWarehouseNameAttribute()
    {
        return $this->warehouse->name;
    }
    public function getServeStatusAttribute()
    {
        return $this->items->where('acceptance_status', '=', ReceivingAcceptanceStatus::PENDING->value)->isEmpty() ? ServeStatus::SERVED->value : ServeStatus::UNSERVED->value;
    }
    public function getRequisitionSlipAttribute()
    {
        $rsId = $this->metadata['rs_id'] ?? null;
        return $rsId ? RequestRequisitionSlip::find($rsId) : null;
    }
    public static function generateNewMrrReferenceNumber()
    {
        $year = now()->year;
        $lastMRR = TransactionMaterialReceiving::orderByRaw('SUBSTRING_INDEX(reference_no, \'-\', -1) DESC')
            ->first();
        $lastRefNo = $lastMRR ? collect(explode('-', $lastMRR->reference_no))->last() : 0;
        $newNumber = str_pad($lastRefNo + 1, 6, '0', STR_PAD_LEFT);
        return "MRR-{$year}-{$newNumber}";
    }
    public function getNetVatAttribute()
    {
        return ($this->unit_price * $this->quantity) ?? 0;
    }
    public function getInputVatAttribute()
    {
        return ($this->unit_price * $this->quantity) ?? 0;
    }
    public function getGrandTotalAttribute()
    {
        return $this->items->sum('grand_total');
    }
}
