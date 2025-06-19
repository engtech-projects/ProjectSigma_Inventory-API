<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceQuotationItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'price_quotation_id',
        'item_id',
        'brand',
        'price',
        'remarks',
    ];
}
