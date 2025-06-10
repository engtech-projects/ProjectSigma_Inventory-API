<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTransactionItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $with = ['uom', 'item'];

    protected $fillable = [
        'item_id',
        'warehouse_transaction_id',
        'parent_id',
        'quantity',
        'uom',
        'metadata',
    ];
    protected $casts = [
        'metadata' => 'array',
    ];
    protected $with = ['uomRelationship', 'item', 'supplier'];

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
    public function uomRelationship()
    {
        return $this->belongsTo(UOM::class, 'uom');
    }
    public function item()
    {
        return $this->belongsTo(ItemProfile::class);
    }
    public function supplier()
    {
        return $this->belongsTo(RequestSupplier::class);
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
