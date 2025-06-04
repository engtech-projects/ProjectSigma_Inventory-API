<?php

namespace App\Models;

use App\Enums\RequestApprovalStatus;
use App\Enums\RequestStatuses;
use App\Enums\RSRemarksEnums;
use App\Enums\TransactionTypes;
use App\Traits\HasApproval;
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

    public function completeRequestStatus()
    {
        $this->request_status = RequestApprovalStatus::APPROVED;
        if ($this->remarks == RSRemarksEnums::PETTYCASH->value) {
            $this->createPettyCashMRR();
        }
        $this->save();
        $this->refresh();
    }

    //create MRR
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
                'equipment_no' => $this->equipment_no,
                'project_code' => $this->section_id,
                'supplier_id' => null,
                'terms_of_payment' => null,
                'particulars' => null,
                'po_id' => null,
                'is_petty_cash' => true,
            ],
            'created_by' => auth()->user()->id,
            'request_status' => RequestApprovalStatus::PENDING,
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
                'specification' => $requestItem->specification,
                'actual_brand_purchase' => null, // Editable field
                'unit_price' => null, // Editable field
                'remarks' => null, // Editable field
                'status' => RequestApprovalStatus::PENDING,
            ];

            WarehouseTransactionItem::create([
                'item_id' => $requestItem->item_id,
                'warehouse_transaction_id' => $mrr->id,
                'parent_id' => null,
                'metadata' => $metadata,
                'quantity' => $requestItem->quantity,
                'uom' => $requestItem->unit,
            ]);
        }
    }

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */
    public function items()
    {
        return $this->hasMany(RequestStockItem::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'office_project', 'id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'office_project_address', 'id');
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

    public function section()
    {
        return $this->morphTo();
    }

    public function getRequestStockItemIdAttribute()
    {
        return WarehouseTransactionItem::whereJsonContains('metadata->request_stock_item_id', $this->id)
            ->value('metadata->request_stock_item_id');
    }



    /**
     * ==================================================
     * LOCAL SCOPES
     * ==================================================
     */


    /**
     * ==================================================
     * DYNAMIC SCOPES
     * ==================================================
     */
}
