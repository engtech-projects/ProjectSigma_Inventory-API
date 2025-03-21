<?php

namespace App\Models;

use App\Enums\RequestStatuses;
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
