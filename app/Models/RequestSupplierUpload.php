<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class RequestSupplierUpload extends Model
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    protected $table = 'request_supplier_uploads';
    public const SUPPLIER_ATTACHMENTS_DIR = "supplier/uploads/";
    protected $fillable = [
        'id',
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
