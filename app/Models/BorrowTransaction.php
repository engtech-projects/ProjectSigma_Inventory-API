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

    public function items()
    {
        return $this->hasMany(BorrowTransactionItems::class, 'borrow_transaction_id');
    }
}
