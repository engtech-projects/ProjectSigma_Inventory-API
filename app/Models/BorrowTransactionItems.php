<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowTransactionItems extends Model
{
    use HasFactory;
    protected $fillable = [
        'borrow_transaction_id',
        'item_id',
        'quantity',
        'metadata',
    ];
    protected $casts = [
        'metadata' => 'array',
    ];
    public function borrowTransaction()
    {
        return $this->belongsTo(BorrowTransaction::class);
    }
    public function item()
    {
        return $this->belongsTo(ItemProfile::class);
    }
}
