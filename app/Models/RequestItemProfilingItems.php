<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItemProfilingItems extends Model
{
    use HasFactory;

    public function requestItemprofiling()
    {
        return $this->belongsTo(RequestItemprofiling::class);
    }

    public function itemProfile()
    {
        return $this->belongsTo(ItemProfile::class);
    }
}
