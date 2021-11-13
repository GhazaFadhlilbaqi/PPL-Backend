<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;
use Vinkla\Hashids\Facades\Hashids;

class Rab extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = ['name', 'project_id'];
    protected $hidden = ['id', 'project_id'];
    protected $appends = ['hashid', 'hashed_project_id'];

    public function rabItemHeader()
    {
        return $this->hasMany(RabItemHeader::class);
    }

    public function rabItem()
    {
        return $this->hasMany(RabItem::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getHashedProjectIdAttribute($value)
    {
        return Hashids::encode($this->project_id);
    }
}
