<?php

namespace App\Enums;

enum AhsSectionEnum: string
{
    case LABOR = "labor";
    case INGREDIENTS = "ingredients";
    case TOOLS = "tools";
    case OTHERS = "others";
}