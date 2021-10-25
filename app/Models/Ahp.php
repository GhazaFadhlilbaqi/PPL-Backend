<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ahp extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $guarded = [];

    public function ahsItem()
    {
        return $this->morphMany(AhsItem::class, 'ahsItemable');
    }
}
