<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class CustomAhs extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = ['code', 'name', 'project_id'];
    protected $appends = ['hashid'];

    public function customAhsItem()
    {
        return $this->hasMany(CustomAhsItem::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function rabItemHeader()
    {
        return $this->hasMany(RabItemHeader::class);
    }
}
