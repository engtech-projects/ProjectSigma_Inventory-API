<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filters
{
    public function bomFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['assignment_id'])) {
            $query->where('assignment_id', $filters['assignment_id']);
        }
        if (!empty($filters['effectivity'])) {
            $query->where('effectivity', $filters['effectivity']);
        }
        return $query;
    }
}
