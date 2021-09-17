<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPrice extends Model
{
    use HasFactory;

    protected $fillable = ['item_price_group_id', 'unit_id', 'name', 'price'];
    protected $hidden = ['id'];
    protected $appends = ['hashid'];

    public function itemPriceGroup()
    {
        return $this->belongsTo(ItemPriceGroup::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
