<?php

namespace App\Models;

use App\Enums\RequestApprovalStatus;
use App\Enums\RequestStatusType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasApproval;

class RequestItemProfiling extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasApproval;


    protected $table = 'request_itemprofiling';
    protected $fillable = [
        'approvals',
        'created_by',
        'request_status',
    ];

    protected $casts = [
        'approvals' => 'array',
    ];

    public function itemProfiles(): HasManyThrough
    {
        return $this->hasManyThrough(
            ItemProfile::class,
            RequestItemProfilingItems::class,
            'request_itemprofiling_id',
            'id',
            'id',
            'item_profile_id'
        );
    }

    public function scopeRequestStatusPending(Builder $query): void
    {
        $query->where('request_status', RequestStatusType::PENDING);
    }

    public function scopeAuthUserPending(Builder $query): void
    {
        // Assuming authUserPending logic
        $query->where('created_by', auth()->user()->id);
    }
    public function completeRequestStatus()
    {
        $this->request_status = RequestApprovalStatus::APPROVED;
        $this->itemProfiles()->update([
            "is_approved" => 1,
        ]);
        $this->save();
        $this->refresh();
    }

}
