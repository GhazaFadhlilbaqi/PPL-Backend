<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ahs extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = ['id', 'name', 'groups'];
    protected $with = ['ahsItem'];

    public function ahsItem()
    {
        return $this->hasMany(AhsItem::class);
    }

    public function ahsItemRef()
    {
        return $this->morphMany(AhsItem::class, 'ahsItemable');
    }
}
