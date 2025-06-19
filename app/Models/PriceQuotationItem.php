<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PriceQuotationItem extends Model
{
    use HasFactory;
    use HasApiTokens;
    use Authorizable;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'price_quotation_id',
        'item_id',
        'brand',
        'price',
        'remarks',
    ];
}
