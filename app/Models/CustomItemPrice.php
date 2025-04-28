<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;
use Vinkla\Hashids\Facades\Hashids;

class CustomItemPrice extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $appends = ['hashid'];
    protected $with = ['unit'];
    protected $fillable = [
        'code', 'custom_item_price_group_id', 'unit_id', 'project_id', 'name', 'is_default', 'price', 'default_price'
    ];

    public function customItemPriceGroup()
    {
        return $this->belongsTo(CustomItemPriceGroup::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
