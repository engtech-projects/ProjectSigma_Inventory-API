<?php

namespace App\Models;

use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SetupWarehouses extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;

    protected $fillable = [
        'id',
        'name',
        'location',
        'owner_id',
        'owner_type',
    ];
    /**
    * ==================================================
    * MODEL ATTRIBUTES
    * ==================================================
    */
    public function getOwnerNameAttribute(): ?string
    {
        return $this->owner?->department_name ?? $this->owner->project_code ?? null;
    }
    /**
    * ==================================================
    * MODEL RELATIONSHIPS
    * ==================================================
    */
    public function warehousePss()
    {
        return $this->hasOneThrough(
            User::class,
            WarehousePss::class,
            'warehouse_id',
            'id',
            'id',
            'user_id'
        );
    }
    public function owner()
    {
        return $this->morphTo();
    }
    public function project()
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id', "id");
    }
    public function department()
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id', "id");
    }
    public function stockSummary()
    {
        return $this->hasMany(WarehouseStocksSummary::class, 'warehouse_id');
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
