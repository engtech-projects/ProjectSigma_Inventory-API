<?php

namespace App\Models;

use App\Traits\HasReferenceNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class PriceQuotation extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasReferenceNumber;

    protected $fillable = [
        'request_procurement_id',
        'supplier_id',
        'metadata',
    ];
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * ==================================================
     * MODEL ACCESSORS
     * ==================================================
     */
    public function getCreatedTimeHumanAttribute()
    {
        return optional($this->created_at)->format('F j, Y');
    }

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
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

    /**
     * ==================================================
     * MODEL SCOPES
     * ==================================================
     */

    /**
     * ==================================================
     * MODEL METHODS
     * ==================================================
     */
    // public static function generateRPQuotationNumber()
    // {
    //     $now = now();
    //     $yearMonth = $now->format('Y-m');

    //     $lastPQ = self::whereYear('created_at', $now->year)
    //         ->whereMonth('created_at', $now->month)
    //         ->where('reference_no', 'like', "RPQ-{$yearMonth}-%")
    //         ->orderBy('created_at', 'desc')
    //         ->first();

    //     if ($lastPQ) {
    //         $lastNumber = (int) substr($lastPQ->reference_no, -4);
    //         $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    //     } else {
    //         $newNumber = '0001';
    //     }

    //     return "RPQ-{$yearMonth}-{$newNumber}";
    // }

}
