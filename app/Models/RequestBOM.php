<?php

namespace App\Models;

use App\Enums\RequestStatuses;
use App\Http\Services\RequestBOMService;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        'version',
        'approvals',
        'created_by',
        'request_status',
    ];

    protected $casts = [
        'approvals' => 'array',
        'effectivity' => 'string',
    ];

    public $appends = [
        'item_summary'
    ];

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->orderByDesc('version')->limit(1);
    }
    public function scopeIsApproved(Builder $query): void
    {
        $query->where('request_status', RequestStatuses::APPROVED);
    }
    public function scopeRequestStatusPending(Builder $query): void
    {
        $query->where('request_status', RequestStatuses::PENDING);
    }

    public function completeRequestStatus()
    {
        $latestVersion = self::where('assignment_type', $this->assignment_type)
            ->where('assignment_id', $this->assignment_id)
            ->where('effectivity', $this->effectivity)
            ->max('version');

        $this->version = $latestVersion ? $latestVersion + 1 : 1;
        $this->request_status = RequestStatuses::APPROVED->value;
        $this->save();
        $this->refresh();
    }

    public function getItemSummaryAttribute()
    {
        return app(RequestBOMService::class)->getItemSummary($this);
    }

    public function getAssignmentType(int $requestBOMId): string
    {
        return self::whereKey($requestBOMId)->value('assignment_type') ?? '';
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
    public function project()
    {
        return $this->morphTo(__FUNCTION__, 'assignment_type', 'assignment_id', "id");
    }
    public function department()
    {
        return $this->morphTo(__FUNCTION__, 'assignment_type', 'assignment_id', "id");
    }
    public function details()
    {
        return $this->hasMany(RequestBomDetails::class, 'request_bom_id', 'id');
    }
    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(
            ItemProfile::class,
            RequestBomDetails::class,
            'request_bom_id',
            'id',
            'id',
            'item_id'
        );
    }

    /**
     * ==================================================
     * DYNAMIC SCOPES
     * ==================================================
     */
}
