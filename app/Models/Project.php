<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;
use Vinkla\Hashids\Facades\Hashids;

class Project extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $appends = ['hashid', 'hashed_province_id', 'activeOrder'];
    protected $hidden = ['id', 'province_id'];

    protected $fillable = [
        'user_id',
        'name',
        'activity',
        'job',
        'address',
        'job',
        'province_id',
        'fiscal_year',
        'profit_margin',
        'last_opened_at',
        'ppn',
        'subscription_id',
        'activeOrder'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function rab()
    {
        return $this->hasMany(Rab::class);
    }

    public function customItemPriceGroup()
    {
        return $this->hasMany(CustomItemPriceGroup::class);
    }

    public function customItemPrice()
    {
        return $this->hasMany(CustomItemPrice::class);
    }

    public function customAhp()
    {
        return $this->hasMany(CustomAhp::class);
    }

    public function customAhs()
    {
        return $this->hasMany(CustomAhs::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function order()
    {
        return $this->hasMany(Order::class);
    }

    public function getHashedProvinceIdAttribute()
    {
        return Hashids::encode($this->province_id);
    }

    public function getActiveOrderAttribute()
    {
        return $this->order->where('is_active', true)->first() ?? null;
    }
}
