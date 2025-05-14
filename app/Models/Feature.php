<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $fillable = ['id', 'name', 'code'];

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'subscription_feature')
                    ->withTimestamps();
    }
}
