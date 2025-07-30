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
        'metadata',
    ];
    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updated(function (WarehouseTransactionItem $item) {
            $item->checkAndUpdateTransactionServeStatus();
        });

        static::created(function (WarehouseTransactionItem $item) {
            $item->checkAndUpdateTransactionServeStatus();
        });

        static::deleted(function (WarehouseTransactionItem $item) {
            $item->checkAndUpdateTransactionServeStatus();
        });
    }
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
        // ensure correct FK and index exist
        return $this->belongsTo(RequestSupplier::class, 'supplier_id');
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
    public function getPerItemTotalAttribute()
    {
        $unitPrice = $this->metadata['unit_price'] ?? 0;
        $quantity = $this->metadata['accepted_quantity'] ?? 0;

        return $unitPrice * $quantity;
    }


    public function getExtPriceAttribute()
    {
        $unitPrice = $this->metadata['unit_price'] ?? 0;
        $quantity = $this->metadata['accepted_quantity'] ?? 0;
        return $unitPrice * $quantity ?? 0;
    }


    // to be used later
    // public function getTotalNetVatAttribute()
    // {
    //     return ($this->metadata['unit_price'] ?? 0) * ($this->quantity ?? 0);
    // }

    // public function getTotalInputVatAttribute()
    // {
    //     return $this->total_net_vat * 0.12; // 12% VAT
    // }

    public function getStatus(): ?string
    {
        return $this->metadata['status'] ?? null;
    }

    public function isAccepted(): bool
    {
        return $this->getStatus() === 'Accepted';
    }

    public function isRejected(): bool
    {
        return $this->getStatus() === 'Rejected';
    }

    public function isAlreadyProcessed(string $status): bool
    {
        return $this->getStatus() === $status;
    }

    public function checkAndUpdateTransactionServeStatus(): void
    {
        if (!$this->transaction) {
            return;
        }
        $transaction = $this->transaction->fresh(['items']);
        if (!$transaction || !$transaction->items->count()) {
            return;
        }
        $allProcessed = $transaction->items->every(function ($item) {
            return $item->isAccepted() || $item->isRejected();
        });
        if ($allProcessed) {
            $metadata = $transaction->metadata ?? [];
            $metadata['serve_status'] = 'Served';
            $transaction->update(['metadata' => $metadata]);
        }
    }

}
