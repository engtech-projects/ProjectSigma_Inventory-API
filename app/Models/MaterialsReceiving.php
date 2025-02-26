<?php

namespace App\Models;

use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MaterialsReceiving extends Model
{
    use HasFactory, SoftDeletes, HasApproval, Notifiable;

    protected $table = 'materials_receiving';
    protected $fillable = [
        'parent_id',
        'parent_type',
        'warehouse_id',
        'reference_no',
        'supplier_id',
        'reference_code',
        'terms_of_payment',
        'particulars',
        'transaction_date',
        'project_id',
        'equipment_no',
        'source_po',
        'total_net_of_vat_cost',
        'total_input_vat',
        'grand_total',
        'created_by',
        'approvals',
        'request_status'
    ];

    protected $casts = [
        "approvals" => 'array'
    ];

    /**
    * ==================================================
    * MODEL RELATIONSHIPS
    * ==================================================
    */

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(RequestSupplier::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function items()
    {
        return $this->hasMany(MaterialsReceivingItem::class);
    }
}
