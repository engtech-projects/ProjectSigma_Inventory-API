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
        'quantity',
        'unit',
        'item_id',
        'specification',
        'preferred_brand',
        'reason',
        'location',
        'location_qty',
        'is_approved',
    ];

    public function requestStock()
    {
        return $this->belongsTo(RequestStock::class);
    }

}
