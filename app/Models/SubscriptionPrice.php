<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPrice extends Model
{
    protected $fillable = [
        'subscription_id',
        'duration_type',
        'price',
        'discounted_price',
        'min_duration',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
