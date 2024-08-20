<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class RequestItemProfiling extends Model
{
    use HasFactory;

    protected $table = 'request_item_profiling';

    protected $casts = [
        'approvals' => 'array',
    ];

    // public function requestItemprofilingItems()
    // {
    //     return $this->hasMany(RequestItemprofilingItems::class);
    // }
    public function requestItemprofiling(): HasManyThrough
    {
        return $this->hasManyThrough(ItemProfile::class, RequestItemProfilingItems::class);
    }
}
