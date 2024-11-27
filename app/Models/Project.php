<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'hrms_id',
        'project_monitoring_id',
        'project_code',
        'status',
    ];

    protected $casts = [
        'project_code' => 'string',
        'project_monitoring_id' => 'integer',
        'created_at' => 'datetime:Y-m-d'
    ];
}
