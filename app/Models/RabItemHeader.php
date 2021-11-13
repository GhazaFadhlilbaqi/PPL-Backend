<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;
use Vinkla\Hashids\Facades\Hashids;

class RabItemHeader extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = ['name', 'rab_id'];
    protected $hidden = ['id', 'rab_id'];
    protected $appends = ['hashid', 'hashed_rab_id'];

    public function rabItem()
    {
        return $this->hasMany(RabItem::class);
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function getHashedRabIdAttribute()
    {
        return Hashids::encode($this->rab_id);
    }
}
