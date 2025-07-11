<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

trait ModelHelpers
{
    /**
     * ==================================================
     * MODEL RELATIONSHIPS
     * ==================================================
     */

    /**
     * ==================================================
     * MODEL ATTRIBUTES
     * ==================================================
     */
    public function getCreatedAtHumanAttribute()
    {
        return Carbon::parse($this->created_at)->format("F j, Y h:i A");
    }
    public function getCreatedAtDateHumanAttribute()
    {
        return Carbon::parse($this->created_at)->format("F j, Y");
    }
    public function getCreatedAtTimeHumanAttribute()
    {
        return Carbon::parse($this->created_at)->format("h:i A");
    }
    /**
     * ==================================================
     * STATIC SCOPES
     * ==================================================
     */

    /**
     * ==================================================
     * DYNAMIC SCOPES
     * ==================================================
     */

    /**
     * ==================================================
     * MODEL FUNCTIONS
     * ==================================================
     */
}
