<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class CustomAhp extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $guarded = [];
    protected $hidden = ['id'];
    protected $appends = ['hashid'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
