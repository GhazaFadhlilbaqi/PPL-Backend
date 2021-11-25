<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class ItemPriceGroup extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $fillable = ['name'];
    protected $hidden = ['id'];
    protected $appends = ['hashid'];

    public function itemPrice()
    {
        return $this->hasMany(ItemPrice::class);
    }

    public function customItemPrice()
    {
        return $this->morphMany(CustomItemPrice::class, 'custom_item_priceable');
    }
}
