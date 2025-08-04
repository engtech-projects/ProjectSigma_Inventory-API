<?php

namespace App\Models;

use App\Http\Traits\HasConversionUnit;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseStockTransactions extends Model
{
    use HasFactory;
    use ModelHelpers;
    use SoftDeletes;
    use HasConversionUnit;

    protected $fillable = [
        'warehouse_id',
        'type',
        'item_id',
        'quantity',
        'uom_id', // UOM ID SHOULD BE SAME OF PARENT FOR STOCKOUT
        'parent_item_id',
        'referenceable_type',
        'referenceable_id',
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
    public function item()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id');
    }
    public function uom()
    {
        return $this->belongsTo(UOM::class, 'uom_id');
    }
    public function parentItem()
    {
        return $this->belongsTo(WarehouseStockTransactions::class, 'parent_item_id');
    }
    public function referenceable()
    {
        return $this->morphTo();
    }
}
