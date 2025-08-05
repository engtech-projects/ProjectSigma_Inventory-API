<?php

namespace App\Models;

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
    public function evaluatedBy()
    {
        return $this->belongsTo(User::class, 'evaluated_by_id');
    }
    public function items()
    {
        return $this->hasMany(TransactionMaterialReceivingItem::class);
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
    public function getWarehouseNameAttribute()
    {
        return $this->warehouse->name;
    }
}
