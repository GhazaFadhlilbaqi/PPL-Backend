<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentHashids\HasHashid;
use Mtvs\EloquentHashids\HashidRouting;

class ItemPriceProvince extends Model
{
    use HasFactory, HasHashid, HashidRouting;

    protected $table = 'item_price_province';
    protected $fillable = ['item_price_id', 'province_id', 'price'];
    protected $hidden = ['id'];
    protected $appends = ['hashid'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function itemPrice()
    {
        return $this->belongsTo(ItemPrice::class);
    }
}
