<?php

namespace App\Models;

use App\Http\Traits\CheckAccessibility;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class RequestProcurement extends Model
{
    use HasFactory;
    use HasApiTokens;
    use Authorizable;
    use Notifiable;
    use SoftDeletes;
    use ModelHelpers;
    use CheckAccessibility;

    protected $table = 'request_procurements';
    protected $fillable = [
        'request_requisition_slip_id',
        'serve_status',
    ];

    public function requestStock()
    {
        return $this->belongsTo(RequestStock::class, 'request_requisition_slip_id');
    }

    public function canvasser()
    {
        return $this->hasOneThrough(
            User::class,
            RequestProcurementCanvasser::class,
            'request_procurement_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function scopeIsUnserved($query)
    {
        return $query->where('serve_status', 'unserved');
    }

    public function scopeIsCanvasser($query, $userId)
    {
        return $query->whereHas('canvasser', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    public function priceQuotations()
    {
        return $this->hasMany(PriceQuotation::class, 'request_procurement_id');
    }
}
