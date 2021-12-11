<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class CustomItemPriceGroup extends Model
{
    use HasFactory, HashidRouting, HasHashid;

    protected $appends = ['hashid'];
    protected $fillable = ['project_id', 'is_default', 'name'];
    protected $hidden = ['id'];

    public function customItemPrice()
    {
        return $this->hasMany(CustomItemPrice::class);
    }
}
