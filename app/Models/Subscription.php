<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class Subscription extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    public $incrementing = false;


    public function features()
    {
        return $this->belongsToMany(Feature::class, 'subscription_feature')
            ->withTimestamps();
    }

    public function prices()
    {
        return $this->hasMany(SubscriptionPrice::class, 'subscription_id');
    }
}
