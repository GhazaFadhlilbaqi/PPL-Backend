<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPrice extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'item_price_group_id', 'unit_id', 'name'];
    protected $with = ['unit'];

    public function itemPriceGroup()
    {
        return $this->belongsTo(ItemPriceGroup::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function province()
    {
        return $this->belongsToMany(Province::class);
    }

    public function price()
    {
        return $this->hasMany(ItemPriceProvince::class);
    }

    public function ahsItem()
    {
        return $this->morphMany(AhsItem::class, 'ahsItemable');
    }

    public function priceByProvince($provinceId)
    {
        return $this->hasOne(ItemPriceProvince::class, 'item_price_id')
            ->where('province_id', $provinceId)
            ->select('id', 'item_price_id', 'price', 'province_id');
    }
}
