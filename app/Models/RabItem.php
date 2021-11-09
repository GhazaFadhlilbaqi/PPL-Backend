<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class RabItem extends Model
{
    use HasFactory, HashidRouting, HasHashid;

    protected $fillable = ['rab_id', 'name', 'ahs_id', 'volume', 'unit_id'];
    protected $hidden = ['id'];
    protected $appends = ['hashid'];

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
