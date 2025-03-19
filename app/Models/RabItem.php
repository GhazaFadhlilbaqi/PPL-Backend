<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;
use Vinkla\Hashids\Facades\Hashids;

class RabItem extends Model
{
    use HasFactory, HashidRouting, HasHashid;

    protected $fillable = [
        'rab_id', 'rab_item_header_id', 'name', 'custom_ahs_id', 'price', 'volume', 'unit_id', 'profit_margin'
    ];

    protected $hidden = [
        'id', 'rab_id', 'rab_item_header_id', 'unit_id'
    ];

    protected $with = ['implementationSchedule'];

    protected $appends = [
        'hashid', 'hashed_rab_item_header_id', 'hashed_rab_id', 'hashed_unit_id'
    ];

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function rabItemHeader()
    {
        return $this->belongsTo(RabItemHeader::class);
    }

    public function customAhs()
    {
        return $this->belongsTo(CustomAhs::class);
    }

    public function getHashedRabItemHeaderIdAttribute()
    {
        return Hashids::encode($this->rab_item_header_id);
    }

    public function getHashedRabIdAttribute()
    {
        return Hashids::encode($this->rab_id);
    }

    public function getHashedUnitIdAttribute()
    {
        return Hashids::encode($this->unit_id);
    }

    public function implementationSchedule()
    {
        return $this->hasMany(ImplementationSchedule::class);
    }
}
