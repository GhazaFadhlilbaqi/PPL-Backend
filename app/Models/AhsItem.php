<?php

namespace App\Models;

use App\Casts\PickItemModelName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhsItem extends Model
{
    use HasFactory;

    protected $fillable = ['ahs_id', 'name', 'unit_id', 'coefficient', 'section', 'ahs_itemable_id', 'ahs_itemable_type'];

    public function ahsItemable()
    {
        return $this->morphTo();
    }

    public function ahs()
    {
        return $this->belongsTo(Ahs::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
