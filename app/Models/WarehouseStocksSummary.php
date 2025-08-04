<?php

namespace App\Models;

use App\Http\Traits\HasConversionUnit;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseStocksSummary extends Model
{
    use HasFactory;
    use ModelHelpers;
    use SoftDeletes;
    use HasConversionUnit;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'quantity', // total quantity
        'uom_id',
        'uom_conversion', // conversion factor for the item in the warehouse
        'metadata',
    ];
    protected $casts = [
        'uom_conversion' => 'array',
        'metadata' => 'array',
    ];
    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function warehouse()
    {
        return $this->belongsTo(SetupWarehouses::class, 'warehouse_id');
    }
    public function item()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id');
    }
    public function uom()
    {
        return $this->belongsTo(UOM::class, 'uom_id');
    }
}
