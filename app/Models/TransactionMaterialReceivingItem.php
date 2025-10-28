<?php

namespace App\Models;

use App\Enums\ServeStatus;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionMaterialReceivingItem extends Model
{
    use HasFactory;
    use ModelHelpers;
    use SoftDeletes;

    protected $fillable = [
        'transaction_material_receiving_id',
        'item_id',
        'specification',
        'actual_brand_purchase',
        'requested_quantity',
        'quantity',
        'uom_id',
        'unit_price',
        'serve_status',
        'remarks',
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
    public function transactionMaterialReceiving()
    {
        return $this->belongsTo(TransactionMaterialReceiving::class);
    }
    public function item()
    {
        return $this->belongsTo(ItemProfile::class, 'item_id');
    }
    public function uom()
    {
        return $this->belongsTo(UOM::class, 'uom_id');
    }
    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getComputedExtPriceAttribute()
    {
        return ($this->unit_price * $this->quantity) ?? 0;
    }
    public function getIsProcessedAttribute()
    {
        return $this->serve_status == ServeStatus::SERVED->value;
    }
    public function getItemCodeAttribute()
    {
        return $this->item->item_code;
    }
    public function getItemDescriptionAttribute()
    {
        return $this->item->item_description;
    }
    public function getNetVatAttribute(): float
    {
        return $this->computed_ext_price > 0 ? round($this->computed_ext_price / 1.12, 2) : 0;
    }
    public function getInputVatAttribute(): float
    {
        return $this->computed_ext_price > 0 ? round($this->computed_ext_price - ($this->computed_ext_price / 1.12), 2) : 0;
    }
    public function getGrandTotalAttribute()
    {
        return $this->net_vat + $this->input_vat;
    }
}
