<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItemProfiling extends Model
{
    use HasFactory;

    protected $table = 'request_item_profiling';

    protected $casts = [
        'approvals' => 'array',
    ];
}
