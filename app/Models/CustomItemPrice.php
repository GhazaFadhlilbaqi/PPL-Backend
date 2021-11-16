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

    protected $hidden = ['id'];
    // protected $hidden = ['id', 'unit_id', 'project_id'];
    // protected $appends = ['hashid', 'hashed_unit_id', 'hashed_project_id'];
    protected $appends = ['hashid'];
    protected $with = ['unit'];
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

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * FIXME: Below methods is commented out because master item price
     * don't encrypt foreign key relation ! make sure to fix it first
     */

    // public function getHashedUnitIdAttribute()
    // {
    //     return Hashids::encode($this->unit_id);
    // }

    // public function getHashedProjectIdAttribute()
    // {
    //     return Hashids::encode($this->project_id);
    // }
}
