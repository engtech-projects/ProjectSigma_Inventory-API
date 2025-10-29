<?php

namespace App\Models;

use App\Enums\RequestStatuses;
use App\Enums\RSRemarksEnums;
use App\Enums\ServeStatus;
use App\Http\Services\MrrService;
use App\Traits\HasApproval;
use App\Traits\HasReferenceNumber;
use App\Traits\ModelHelpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestRequisitionSlip extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;
    use HasApproval;
    use HasReferenceNumber;

    protected $fillable = [
        'reference_no',
        'request_for',
        'warehouse_id',
        'section_id',
        'section_type',
        'office_project_address',
        'date_prepared',
        'date_needed',
        'equipment_no',
        'type_of_request',
        'contact_no',
        'remarks',
        'current_smr',
        'previous_smr',
        'unused_smr',
        'next_smr',
        'created_by',
        'approvals',
        'request_status'
    ];

    protected $casts = [
        'approvals' => 'array',
        'metadata' => 'array'
    ];

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getConvertableUnitsAttribute()
    {
        return $this->items->map(function ($item) {
            return $item->itemProfile->convertable_units ?? [];
        })->collapse()->unique('id')->values();
    }
    public function getUomNameAttribute()
    {
        return $this->uom?->name;
    }
    public function getProjectCodeAttribute()
    {
        return $this->project->project_code ?? null;
    }

    public function getDateNeededHumanAttribute()
    {
        return $this->date_needed ? Carbon::parse($this->date_needed)->format("F j, Y") : null;
    }

    public function getDatePreparedHumanAttribute()
    {
        return $this->date_prepared ? Carbon::parse($this->date_prepared)->format("F j, Y") : null;
    }

    public function getProjectDepartmentNameAttribute(): ?string
    {
        return match ($this->section_type) {
            'Project' => $this->project?->project_code,
            'Department' => $this->department?->department_name,
            default => null,
        };
    }
    public function getServeStatusAttribute()
    {
        $relatedItems = TransactionMaterialReceivingItem::whereHas('transactionMaterialReceiving', function ($query) {
            $query->where('metadata->rs_id', $this->id);
        })->pluck('serve_status');

        if ($relatedItems->isEmpty()) {
            return null;
        }
        return $relatedItems->contains(ServeStatus::UNSERVED->value)
            ? ServeStatus::UNSERVED->value
            : ServeStatus::SERVED->value;
    }

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function uom()
    {
        return $this->belongsTo(UOM::class);
    }
    public function items()
    {
        return $this->hasMany(RequestRequisitionSlipItems::class, 'request_requisition_slip_id', 'id');
    }
    public function section()
    {
        return $this->morphTo();
    }
    public function project()
    {
        return $this->morphTo(__FUNCTION__, 'section_type', 'section_id', "id");
    }
    public function department()
    {
        return $this->morphTo(__FUNCTION__, 'section_type', 'section_id', "id");
    }
    public function currentBom()
    {
        return $this->hasMany(RequestBOM::class, 'assignment_id', 'section_id')
            ->where('request_status', RequestStatuses::APPROVED)
            ->latest("version");
    }
    public function itemProfiles()
    {
        return $this->hasManyThrough(
            ItemProfile::class,
            RequestRequisitionSlipItems::class,
            'request_requisition_slip_id',
            'id',
            'id',
            'item_id'
        );
    }
    public function requestProcurement()
    {
        return $this->hasOne(RequestProcurement::class, 'request_requisition_slip_id');
    }
    public function warehouse()
    {
        return $this->belongsTo(SetupWarehouses::class, 'warehouse_id');
    }
    public function transactionMaterialReceiving()
    {
        return $this->hasMany(TransactionMaterialReceiving::class, 'request_requisition_slip_id');
    }

    /**
     * ==================================================
     * LOCAL SCOPES
     * ==================================================
     */

    /**
     * ==================================================
     * MODEL FUNCTIONS
     * ==================================================
     */
    public function completeRequestStatus()
    {
        $this->request_status = RequestStatuses::APPROVED;
        if ($this->remarks == RSRemarksEnums::PETTYCASH->value) {
            $mrrService = new MrrService(new TransactionMaterialReceiving());
            $mrrService->createPettyCashMrrFromRequestRequisitionSlip($this);
        } elseif ($this->remarks == RSRemarksEnums::PURCHASEORDER->value) {
            $this->createProcurementRequest();
        }
        $this->save();
        $this->refresh();
    }
    public function createProcurementRequest()
    {
        return $this->requestProcurement()->create([
            'serve_status' => ServeStatus::UNSERVED->value
        ]);
    }
}
