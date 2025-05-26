<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTransactionItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'item_id',
        'warehouse_transaction_id',
        'parent_id',
        'quantity',
        'uom',
    ];
    protected $casts = [
        'metadata' => 'array',
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
    public function transaction()
    {
        return $this->belongsTo(WarehouseTransaction::class, 'warehouse_transaction_id');
    }
    public function uom()
    {
        return $this->belongsTo(UOM::class);
    }
    public function item()
    {
        return $this->belongsTo(ItemProfile::class);
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
