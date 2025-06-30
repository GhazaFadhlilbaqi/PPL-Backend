<?php

namespace App\Enums;

enum SubscriptionType: string
{
    case DEMO = 'demo';
    case STUDENT = 'student';
    case STARTER = 'starter';
    case PROFESSIONAL = 'professional';
}