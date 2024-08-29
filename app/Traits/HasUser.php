<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;

trait HasUser
{
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }
}
