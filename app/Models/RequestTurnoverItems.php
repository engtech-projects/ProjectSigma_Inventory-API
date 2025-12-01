<?php

namespace App\Models;

use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestTurnoverItems extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;
    protected $fillable = [
        'request_turnover_id',
        'item_id',
        'quantity',
        'uom',
        'condition',
        'remarks',
        'accept_status',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    // Relationships
    public function requestTurnover(): BelongsTo
    {
        return $this->belongsTo(RequestTurnover::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemProfile::class);
    }
    public function uom()
    {
        return $this->belongsTo(UOM::class);
    }
    // Helpers
    public function isPending(): bool
    {
        return $this->accept_status === 'Pending';
    }

    public function isAccepted(): bool
    {
        return $this->accept_status === 'Accepted';
    }

    public function isDenied(): bool
    {
        return $this->accept_status === 'Denied';
    }

    public function canBeAccepted(): bool
    {
        return $this->isPending() && $this->requestTurnover->isPending();
    }

    public function canBeDenied(): bool
    {
        return $this->isPending() && $this->requestTurnover->isPending();
    }

    public function getUomNameAttribute()
    {
        return UOM::find($this->uom)?->name;
    }
}
