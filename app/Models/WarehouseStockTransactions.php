<?php

namespace App\Models;

use App\Enums\StockTransactionTypes;
use App\Http\Traits\HasConversionUnit;
use App\Observers\WarehouseStockTransactionsObserver;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([WarehouseStockTransactionsObserver::class])]
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
        'uom_conversion',
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
    public function children()
    {
        return $this->hasMany(WarehouseStockTransactions::class, 'parent_item_id');
    }
    public function referenceable()
    {
        return $this->morphTo();
    }
    /**
    * ==================================================
    * MODEL ATTRIBUTES
    * ==================================================
    */
    public function getMovementAttribute()
    {
        $movementIcon = $this->type == StockTransactionTypes::STOCKIN->value ? '+' : '-';
        return $movementIcon." ".$this->quantity." ". $this->item->uom_full_name;
    }

    public function getRemainingStockAttribute()
    {
        $totalStockout = $this->children()
            ->where('type', StockTransactionTypes::STOCKOUT->value)
            ->sum('quantity');
        return $this->quantity - $totalStockout;
    }
}
