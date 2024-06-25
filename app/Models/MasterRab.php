<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class MasterRab extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = ['name', 'master_rab_category_id'];
    protected $hidden = ['id'];
    protected $appends = ['hashid'];

    public function masterRabItemHeader()
    {
        return $this->hasMany(MasterRabItemHeader::class);
    }

    public function masterRabItem()
    {
        return $this->hasMany(MasterRabItem::class);
    }
}
