<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class PriceQuotation extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'request_procurement_id',
        'supplier_id',
        'metadata',
    ];

    public function items()
    {
        return $this->hasMany(PriceQuotationItem::class);
    }

    public function requestProcurement()
    {
        return $this->belongsTo(RequestProcurement::class);
    }

    public function supplier()
    {
        return $this->belongsTo(RequestSupplier::class, 'supplier_id');
    }
}
