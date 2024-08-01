<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'setup_item_groups';
    protected $fillable = [
        'id',
        'name',
        'sub_groups',
    ];
    protected $casts = [
        'sub_groups' => 'array',
    ];
}
