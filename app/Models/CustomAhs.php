<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class CustomAhs extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = ['code', 'name'];
    protected $hidden = ['id'];
    protected $appends = ['hashid'];

    public function customAhsItem()
    {
        return $this->hasMany(CustomAhsItem::class);
    }
}
