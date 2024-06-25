<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;
use Vinkla\Hashids\Facades\Hashids;

class MasterRabItem extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = [
        'master_rab_id', 'master_rab_item_header_id', 'name', 'ahs_id', 'price', 'volume', 'unit_id'
    ];

    protected $hidden = [
        'id', 'master_rab_id', 'master_rab_item_header_id', 'unit_id'
    ];

    protected $appends = [
        'hashid', 'hashed_master_rab_item_header_id', 'hashed_master_rab_id', 'hashed_unit_id'
    ];

    public function masterRab()
    {
        return $this->belongsTo(MasterRab::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function masterRabItemHeader()
    {
        return $this->belongsTo(MasterRabItemHeader::class);
    }

    public function ahs()
    {
        return $this->belongsTo(Ahs::class);
    }

    public function getHashedMasterRabItemHeaderIdAttribute()
    {
        return Hashids::encode($this->master_rab_item_header);
    }

    public function getHashedMasterRabIdAttribute()
    {
        return Hashids::encode($this->master_rab_id);
    }

    public function getHashedUnitIdAttribute()
    {
        return Hashids::encode($this->unit_id);
    }
}
