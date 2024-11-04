<?php

namespace App\Models;

use App\Enums\RequestApprovalStatus;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class RequestBOM extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasApproval;


    protected $table = 'request_bom';
    protected $fillable = [
        'assignment_id',
        'assignment_type',
        'effectivity',
        'approvals',
        'created_by',
        'request_status',
    ];

    protected $casts = [
        'approvals' => 'array',
        'effectivity' => 'string',
    ];

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function scopeRequestStatusPending(Builder $query): void
    {
        $query->where('request_status', RequestApprovalStatus::PENDING);
    }

    public function scopeAuthUserPending(Builder $query): void
    {
        // Assuming authUserPending logic
        $query->where('created_by', auth()->user()->id);
    }

    public function completeRequestStatus()
    {
        $this->request_status = RequestApprovalStatus::APPROVED;
        $this->save();
        $this->refresh();
    }
    /**
    * ==================================================
    * MODEL RELATIONSHIPS
    * ==================================================
    */
    public function assignment()
    {
        return $this->morphTo();
    }

    public function details()
    {
        return $this->hasMany(Details::class, 'request_bom_id');
    }

    /**
    * ==================================================
    * DYNAMIC SCOPES
    * ==================================================
    */
}
