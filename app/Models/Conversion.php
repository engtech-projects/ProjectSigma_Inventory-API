<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    use HasFactory;

    protected $table = 'conversion';

    public function uomGroup()
    {
        return $this->belongsTo(UOMGroup::class, 'uom_group_id');
    }
}
