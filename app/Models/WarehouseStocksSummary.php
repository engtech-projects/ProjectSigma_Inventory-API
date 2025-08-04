<?php

namespace App\Models;

use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseStocksSummary extends Model
{
    use HasFactory;
    use ModelHelpers;
    use SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'total_quantity',
        'uom_id',
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
        return $this->belongsTo(SetupWarehouses::class, 'warehouse_id', 'wss_warehouse');
    }
    public function item()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id', 'wss_item');
    }
    public function uom()
    {
        return $this->belongsTo(UOM::class, 'uom_id', 'wss_uom');
    }
}
