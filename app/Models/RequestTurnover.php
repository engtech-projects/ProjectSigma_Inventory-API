<?php

namespace App\Models;

use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestTurnover extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;
    protected $fillable = [
        'reference_no',
        'date',
        'from_warehouse_id',
        'to_warehouse_id',
        'requested_by',
        'approved_by',
        'received_date',
        'received_name',
        'approval_status',
        'remarks',
        'metadata',
    ];
    protected $casts = [
        'date' => 'date',
        'received_date' => 'date',
        'metadata' => 'array',
    ];
    // Relationships
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(SetupWarehouses::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(SetupWarehouses::class, 'to_warehouse_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequestTurnoverItems::class);
    }

    // Scopes
    public function scopeIncoming(Builder $query, int $warehouseId): Builder
    {
        return $query->where('to_warehouse_id', $warehouseId);
    }

    public function scopeOutgoing(Builder $query, int $warehouseId): Builder
    {
        return $query->where('from_warehouse_id', $warehouseId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('approval_status', 'rejected');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function hasBeenReceived(): bool
    {
        return !is_null($this->received_date) && !is_null($this->received_name);
    }

    public function getTotalItemsCount(): int
    {
        return $this->items()->count();
    }

    public function getAcceptedItemsCount(): int
    {
        return $this->items()->where('accept_status', 'accepted')->count();
    }

    public function getDeniedItemsCount(): int
    {
        return $this->items()->where('accept_status', 'denied')->count();
    }

    public function getPendingItemsCount(): int
    {
        return $this->items()->where('accept_status', 'pending')->count();
    }

    public function canBeUpdated(): bool
    {
        return $this->isPending();
    }

    public static function generateReferenceNo(): string
    {
        $prefix = 'RT';
        $date = now()->format('Ymd');
        $lastRequest = self::whereDate('created_at', today())
            ->latest('id')
            ->first();

        $sequence = $lastRequest ? (int) substr($lastRequest->reference_no, -4) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_no)) {
                $model->reference_no = self::generateReferenceNo();
            }
        });
    }
}
