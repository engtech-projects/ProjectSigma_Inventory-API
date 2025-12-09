<?php

namespace App\Models;

use App\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SetupProjects extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ModelHelpers;
    protected $fillable = [
        'project_code',
        'status',
    ];

    /**
     *
     * MODEL RELATIONSHIPS
     *
     */
    public function warehouse()
    {
        return $this->morphOne(SetupWarehouses::class, 'owner');
    }
}
