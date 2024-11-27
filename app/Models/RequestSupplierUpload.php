<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestSupplierUpload extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'request_supplier_uploads';
    protected $fillable = [
        'request_supplier_id',
        'attachment_name',
        'file_location'
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
    public function requestSupplier()
    {
        return $this->belongsTo(RequestSupplier::class, 'request_supplier_id');
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
