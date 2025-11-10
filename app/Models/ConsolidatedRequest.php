<?php

namespace App\Models;

use App\Traits\HasReferenceNumber;
use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsolidatedRequest extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;
    use HasReferenceNumber;

    protected $fillable = [
        'reference_no',
        'purpose',
        'consolidated_by',
        'date_consolidated',
        'status',
        'remarks',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'date_consolidated' => 'date',
    ];

    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */

    public function items()
    {
        return $this->hasMany(ConsolidatedRequestItems::class, 'consolidated_request_id');
    }
    public function slips()
    {
        return $this->hasManyThrough(
            RequestRequisitionSlip::class,
            ConsolidatedRequestItems::class,
            'consolidated_request_id',
            'id',
            'id',
            'requisition_slip_id'
        );
    }
}
