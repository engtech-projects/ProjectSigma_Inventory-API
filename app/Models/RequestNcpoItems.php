<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ModelHelpers;

class RequestNcpoItems extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;

    protected $fillable = [
        'request_ncpo_id',
        'item_id',
        'changed_supplier_id',
        'changed_item_description',
        'changed_specification',
        'changed_qty',
        'changed_uom',
        'changed_unit_price',
        'changed_brand',
        'new_total',
        'cancel_item',
        'metadata'
    ];

    protected $casts = [
        'new_total' => 'float',
        'cancel_item' => 'boolean',
    ];

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function requestNcpo()
    {
        return $this->belongsTo(RequestNCPO::class, 'request_ncpo_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id');
    }

    public function supplier()
    {
        return $this->belongsTo(RequestSupplier::class, 'changed_supplier_id');
    }
}
