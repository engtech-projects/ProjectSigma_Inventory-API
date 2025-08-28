<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestWithdrawal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date_time',
        'warehouse_id',
        'chargeable_id',
        'chargeable_type',
        'equipment_no',
        'smr',
        'fuel',
        'reference_no',
        'metadata',
        'approvals',
        'request_status',
        'created_by',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'metadata' => 'array',
        'approvals' => 'array',
    ];

    // Relationships
    public function warehouse()
    {
        return $this->belongsTo(SetupWarehouses::class, 'warehouse_id');
    }

    public function chargeable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
