<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
* @OA\Schema(
*     schema="Warehouse",
*     type="object",
*     title="Warehouse",
*     @OA\Property(property="id", type="integer", format="int64", example=1),
*     @OA\Property(property="name", type="string", example="Main Warehouse"),
*     @OA\Property(property="location", type="string", example="123 Warehouse St."),
*     @OA\Property(property="owner_id", type="integer", format="int64", example=1),
*     @OA\Property(property="owner_type", type="string", example="Company"),
*     @OA\Property(property="created_at", type="string", format="date-time"),
*     @OA\Property(property="updated_at", type="string", format="date-time"),
*     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
* )
*/
class Warehouse extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'warehouse';

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


    /**
    * ==================================================
    * MODEL RELATIONSHIPS
    * ==================================================
    */
    public function warehousePss()
    {
        return $this->hasOne(WarehousePss::class);
    }
    public function transactionItems()
    {
        return $this->hasManyThrough(WarehouseTransactionItem::class, WarehouseTransaction::class);
    }
    public function warehouseTransactions()
    {
        return $this->hasMany(WarehouseTransaction::class);
    }
    public function materialsReceivingItems()
    {
        return $this->hasMany(MaterialsReceivingItem::class);
    }

    public function materialsReceiving()
    {
        return $this->hasMany(MaterialsReceiving::class);
    }

    public function requestStocks()
    {
        return $this->hasMany(RequestStock::class);
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
