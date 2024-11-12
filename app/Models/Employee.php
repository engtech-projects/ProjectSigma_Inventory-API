<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'hrms_id',
        'first_name',
        'middle_name',
        'family_name',
        'name_suffix',
        'nick_name',
        'gender',
        'date_of_birth',
        'place_of_birth',
        'citizenship',
        'blood_type',
        'civil_status',
        'date_of_marriage',
        'telephone_number',
        'mobile_number',
        'email',
        'religion',
        'weight',
        'height',
    ];
    protected $appends = [
        'fullname_last',
        'fullname_first',
    ];

    protected function getFullnameLastAttribute(): string
    {
        return "{$this->family_name}, {$this->first_name} {$this->middle_name} {$this->name_suffix}";
    }

    protected function getFullnameFirstAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->family_name} {$this->name_suffix}");
    }
}
