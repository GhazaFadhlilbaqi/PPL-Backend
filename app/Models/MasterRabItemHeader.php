<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;
use Vinkla\Hashids\Facades\Hashids;

class MasterRabItemHeader extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = [
        'name', 'master_rab_id'
    ];

    protected $hidden = [
        'id', 'master_rab_id'
    ];

    protected $appends = [
        'hashid', 'hashed_master_rab_id'
    ];

    public function masterRabItem()
    {
        return $this->hasMany(MasterRabItem::class);
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function getHashedMasterRabIdAttribute()
    {
        return Hashids::encode($this->master_rab_id);
    }
}
