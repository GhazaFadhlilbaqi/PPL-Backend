<?php

namespace App\Enums;

enum OrderType: string
{
    case CREATE = 'create';
    case RENEW = 'renew';
}