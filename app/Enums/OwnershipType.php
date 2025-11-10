<?php

namespace App\Enums;

enum OwnershipType: string
{
    case SINGLE = "Single Proprietorship";
    case PARTNERSHIP = "Partnership";
    case CORPORATION = "Corporation";
}
