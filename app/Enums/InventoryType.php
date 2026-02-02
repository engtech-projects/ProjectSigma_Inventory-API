<?php

namespace App\Enums;

enum InventoryType: string
{
    case INVENTORIABLE = "Inventoriable";
    case NONINVENTORIABLE = "Non-Inventoriable";
}
