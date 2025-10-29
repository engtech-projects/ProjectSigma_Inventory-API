<?php

namespace App\Enums;

enum RequestTypes: string
{
    case CONSOLIDATED = "Consolidated Request for the month of";
    case RECOMMENDED = "Recommended Request";
    case SPECIAL = "Special Case of Request";
    case NOTAPPLICABLE = "N/A";
}
