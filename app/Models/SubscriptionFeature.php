<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionFeature extends Model
{
    protected $fillable = [
        'subscription_id',
        'duration_type',
        'price',
        'discounted_price',
        'min_duration',
    ];

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class);
    }
}
