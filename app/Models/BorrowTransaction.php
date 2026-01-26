<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasApproval;
use App\Traits\HasReferenceNumber;
use App\Traits\ModelHelpers;

class BorrowTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;
    use HasApproval;
    use HasReferenceNumber;

    protected $fillable = [
        'reference_no',
        'warehouse_id',
        'date_time_borrowed',
        'borrowed_by',
        'borrowed_contact_no',
        'returned_by',
        'date_time_returned',
        'received_by',
        'remarks',
        'metadata',
        'approvals',
        'request_status',
        'created_by',
    ];

    protected $casts = [
        'approvals' => 'array',
        'metadata' => 'array'
    ];
    /**
    * ==================================================
    * MODEL RELATIONSHIPS
    * ==================================================
    */
    public function items()
    {
        return $this->hasMany(BorrowTransactionItems::class, 'borrow_transaction_id');
    }
    public function warehouse()
    {
        return $this->belongsTo(SetupWarehouses::class);
    }
    public function borrowedBy()
    {
        return $this->belongsTo(SetupEmployees::class, 'borrowed_by');
    }
    public function returnedBy()
    {
        return $this->belongsTo(SetupEmployees::class, 'returned_by');
    }
    public function receivedBy()
    {
        return $this->belongsTo(SetupEmployees::class, 'received_by');
    }
}
