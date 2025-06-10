<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ahs extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';
    protected $fillable = ['code', 'name', 'reference_group_id'];
    protected $with = ['ahsItem'];

    public function ahsItem()
    {
        return $this->hasMany(AhsItem::class);
    }

    public function ahsItemRef()
    {
        return $this->morphMany(AhsItem::class, 'ahsItemable');
    }

    public function referenceGroup() {
        return $this->belongsTo(AhsReferenceGroup::class, 'reference_group_id');
    }
}
