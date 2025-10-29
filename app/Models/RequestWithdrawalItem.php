<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestWithdrawalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_withdrawal_id',
        'item_id',
        'quantity',
        'uom_id',
        'purpose_of_withdrawal',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'quantity' => 'decimal:2',
    ];

    // Relationships
    public function requestWithdrawal()
    {
        return $this->belongsTo(RequestWithdrawal::class, 'request_withdrawal_id');
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
