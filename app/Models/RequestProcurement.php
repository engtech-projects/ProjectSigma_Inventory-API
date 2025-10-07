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

    public function requisitionSlip()
    {
        return $this->belongsTo(RequestRequisitionSlip::class, 'request_requisition_slip_id');
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

    public function canvassSummaries()
    {
        return $this->hasManyThrough(
            RequestCanvassSummary::class,
            PriceQuotation::class,
            'request_procurement_id',
            'price_quotation_id',
            'id',
            'id'
        );
    }
    public function getPurchaseOrdersAttribute()
    {
        return $this->priceQuotations
            ->flatMap(fn ($pq) => $pq->canvassSummaries)
            ->map(fn ($cs) => $cs->purchaseOrder)
            ->filter()
            ->unique('id')
            ->sortByDesc('created_at')
            ->values();
    }
    public function getNcpoAttribute()
    {
        if (!$this->relationLoaded('priceQuotations')) {
            return collect();
        }
        return $this->priceQuotations
            ->loadMissing(['canvassSummaries.purchaseOrder.ncpos'])
            ->flatMap(fn ($pq) => $pq->canvassSummaries)
            ->map(fn ($cs) => $cs->purchaseOrder)
            ->filter()
            ->flatMap(fn ($po) => $po->ncpos ?? collect())
            ->filter()
            ->unique('id')
            ->sortByDesc('created_at')
            ->values();
    }
}
