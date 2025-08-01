<?php

namespace App\Models;

use App\Enums\RequestStatuses;
use App\Enums\RSRemarksEnums;
use App\Enums\TransactionTypes;
use App\Traits\HasApproval;
use App\Traits\HasReferenceNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class RequestStock extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasApproval;
    use HasReferenceNumber;

    protected $table = 'request_stocks';

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
        return $this->hasMany(RequestStockItem::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'section_id', 'id');
    }
    public function department()
    {
        return $this->belongsTo(SetupDepartments::class, 'section_id', 'id');
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
            RequestStockItem::class,
            'request_stock_id',
            'id',
            'id',
            'item_id'
        );
    }

    public function getDateNeededHumanAttribute()
    {
        return $this->date_needed ? Carbon::parse($this->date_needed)->format("F j, Y") : null;
    }

    public function getDatePreparedHumanAttribute()
    {
        return $this->date_prepared ? Carbon::parse($this->date_prepared)->format("F j, Y") : null;
    }

    public function requestProcurement()
    {
        return $this->hasOne(RequestProcurement::class, 'request_requisition_slip_id');
    }
    public function getProjectDepartmentNameAttribute(): ?string
    {
        return match ($this->section_type) {
            'Project' => $this->project?->project_code,
            'Department' => $this->department?->department_name,
            default => null,
        };
    }

    public function section()
    {
        return $this->morphTo();
    }

    /**
     * ==================================================
     * LOCAL SCOPES
     * ==================================================
     */
    public function completeRequestStatus()
    {
        $this->request_status = RequestStatuses::APPROVED;
        if ($this->remarks == RSRemarksEnums::PETTYCASH->value) {
            $this->createPettyCashMRR();
        } elseif ($this->remarks == RSRemarksEnums::PURCHASEORDER->value) {
            $this->createProcurementRequest();
        }
        $this->save();
        $this->refresh();
    }
    // create MRR
    public function createPettyCashMRR()
    {
        $mrrReferenceNo = $this->generateMRRReferenceNumber();

        $mrr = WarehouseTransaction::create([
            'reference_no' => $mrrReferenceNo,
            'warehouse_id' => $this->warehouse_id,
            'transaction_type' => TransactionTypes::RECEIVING,
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'charging_id' => $this->id,
            'charging_type' => null,
            'approvals' => $this->approvals,
            'metadata' => [
                'rs_id' => $this->id,
                'po_id' => null,
                'supplier_id' => null,
                'reference' => $this->reference_no,
                'equipment_no' => $this->equipment_no,
                'terms_of_payment' => null,
                'particulars' => null,
                'serve_status' => 'Unserved',
                'is_petty_cash' => true,
            ],
            'created_by' => auth()->user()->id,
            'request_status' => RequestStatuses::APPROVED,
        ]);

        $this->storeItems($mrr);

        return $mrr;
    }

    private function generateMRRReferenceNumber()
    {
        $year = now()->year;
        $lastMRR = WarehouseTransaction::where('transaction_type', TransactionTypes::RECEIVING)
            ->whereYear('created_at', $year)
            ->where('reference_no', 'like', "MRR-{$year}-%")
            ->orderBy('reference_no', 'desc')
            ->first();

        if ($lastMRR) {
            $lastNumber = (int) substr($lastMRR->reference_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "MRR-{$year}-CENTRAL-{$newNumber}";
    }

    private function storeItems($mrr)
    {
        foreach ($this->items as $requestItem) {
            $metadata = [
                'requested_quantity' => $requestItem->quantity,
                'specification' => $requestItem->specification,
                'actual_brand_purchase' => $requestItem->preferred_brand,
                'unit_price' => null, // Editable field
                'remarks' => null, // Editable field
                'status' => null,
                'ext_price' => null,
            ];

            WarehouseTransactionItem::create([
                'item_id' => $requestItem->item_id,
                'warehouse_transaction_id' => $mrr->id,
                'parent_id' => null,
                'metadata' => $metadata,
                'quantity' => 0,
                'uom' => $requestItem->unit,
            ]);
        }
    }

    // create procurement request
    public function createProcurementRequest()
    {
        return $this->requestProcurement()->create([
            'serve_status' => 'unserved'
        ]);
    }

    /**
     * ==================================================
     * DYNAMIC SCOPES
     * ==================================================
     */
}
