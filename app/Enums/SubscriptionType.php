<?php

namespace App\Enums;

enum SubscriptionType: string
{
    case STUDENT = 'student';
    case STARTER = 'starter';
    case PROFESSIONAL = 'professional';
}