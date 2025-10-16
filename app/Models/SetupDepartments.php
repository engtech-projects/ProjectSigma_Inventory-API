<?php

namespace App\Models;

use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SetupDepartments extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;

    protected $fillable = [
        'code',
        'department_name',
    ];
}
