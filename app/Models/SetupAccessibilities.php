<?php

namespace App\Models;

use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SetupAccessibilities extends Model
{
    use HasFactory;
    use ModelHelpers;
    use SoftDeletes;

    protected $table = 'setup_accessibilities';

    protected $fillable = [
        "accessibilities_name",
        "created_at",
        "updated_at",
        "deleted_at",
    ];
}
