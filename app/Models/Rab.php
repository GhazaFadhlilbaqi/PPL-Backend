<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class Rab extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = ['name', 'project_id'];
    protected $hidden = ['id'];
    protected $appends = ['hashid'];

    public function rabItem()
    {
        return $this->hasMany(RabItem::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
