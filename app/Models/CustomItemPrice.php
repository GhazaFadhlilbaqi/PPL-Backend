<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class CustomItemPrice extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $hidden = ['id'];
    protected $appends = ['hashid'];
    protected $fillable = [
        'code', 'custom_item_priceable_id', 'custom_item_priceable_type', 'unit_id', 'project_id', 'name', 'price'
    ];

    public function customItemPriceable()
    {
        return $this->morphTo();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
