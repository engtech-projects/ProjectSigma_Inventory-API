<?php

namespace App\Models;

use App\Enums\RequestStatuses;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class RequestSupplier extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasApproval;
    use Notifiable;
    protected $table = 'request_supplier';
    protected $fillable = [
        'supplier_code',
        'company_name',
        'company_address',
        'company_contact_number',
        'company_email',
        'contact_person_name',
        'contact_person_number',
        'contact_person_designation',
        'type_of_ownership',
        'nature_of_business',
        'products_services',
        'classification',
        'tin',
        'terms_and_conditions',
        // 'filled_by',
        // 'filled_designation',
        // 'filled_date',
        // 'requirements_complete',
        'remarks',
        'metadata',
        'created_by',
        'approvals',
        'request_status'
    ];
    protected $casts = [
        'approvals' => 'array',
    ];

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getCodeNameAttribute()
    {
        return '[' . $this->supplier_code . '] ' . $this->company_name;
    }
    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function uploads()
    {
        return $this->hasMany(RequestSupplierUpload::class);
    }

    /**
     * ==================================================
     * LOCAL SCOPES
     * ==================================================
     */
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
        $this->request_status = RequestStatuses::APPROVED;
        $this->save();
        $this->refresh();
    }

    /**
     * ==================================================
     * DYNAMIC SCOPES
     * ==================================================
     */
}
