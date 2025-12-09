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
        if (!$this->created_at) {
            return null;
        }
        return Carbon::parse($this->created_at)->format("F j, Y h:i A");
    }
    public function getCreatedAtDateHumanAttribute()
    {
        if (!$this->created_at) {
            return null;
        }
        return Carbon::parse($this->created_at)->format("F j, Y");
    }
    public function getCreatedAtTimeHumanAttribute()
    {
        if (!$this->created_at) {
            return null;
        }
        return Carbon::parse($this->created_at)->format("h:i A");
    }

    public function formatReadableDate($date)
    {
        if (!$date) {
            return null;
        }
        return Carbon::parse($date)->format("F j, Y");
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
