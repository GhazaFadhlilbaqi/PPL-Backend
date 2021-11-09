<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class RabItemHeader extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = ['name', 'rab_id'];
    protected $hidden = ['id'];
    protected $appends = ['hashid'];

    public function rabItem()
    {
        return $this->hasMany(RabItem::class);
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
