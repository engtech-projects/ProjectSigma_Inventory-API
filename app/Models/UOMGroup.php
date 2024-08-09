<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UOMGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'setup_uom_group';
    protected $fillable = [
        'id',
        'name',
    ];

}
