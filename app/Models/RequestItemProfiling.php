<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class RequestItemProfiling extends Model
{
    use HasFactory;

    protected $table = 'request_itemprofiling';
    protected $fillable = [
        'approvals',
        'created_by',
    ];

    protected $casts = [
        'approvals' => 'array',
    ];

    public function itemProfiles(): HasManyThrough
    {
        return $this->hasManyThrough(
            ItemProfile::class,
            RequestItemProfilingItems::class,
            'request_itemprofiling_id',
            'id',
            'id',
            'item_profile_id'
        );
    }

}
