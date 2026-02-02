<?php

namespace App\Enums;

enum TurnoverSource: string
{
    case DEPARTMENT = 'Department';
    case PROJECT = 'Project';
    case EMPLOYEE = 'Employee';
}
