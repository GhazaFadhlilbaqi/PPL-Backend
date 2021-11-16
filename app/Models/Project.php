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

    protected $appends = ['hashid', 'hashed_province_id'];
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
        'last_opened_at'
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

    public function getHashedProvinceIdAttribute()
    {
        return Hashids::encode($this->province_id);
    }
}
