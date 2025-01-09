<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStockItem extends Model
{
    use HasFactory;

    protected $table = 'request_stocks_items';

    protected $fillable = [
        'request_stock_id',
        'item_id',
        'qty',
        'uom',
        'item_description',
        'specification',
        'preferred_brand',
        'reason',
        'location',
        'is_approved',
        'type_of_request',
        'contact_no',
        'remarks',
        'current_smr',
        'previous_smr',
        'unused_smr',
        'next_smr',
    ];

    public function requestStock()
    {
        return $this->belongsTo(RequestStock::class);
    }
}
