<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class MasterRabCategory extends Model
{
    use HasFactory, HasHashid;

    protected $guarded = [];

    public function masterRab()
    {
        return $this->hasMany(MasterRab::class);
    }
}
