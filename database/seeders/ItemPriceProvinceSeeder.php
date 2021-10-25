<?php

namespace Database\Seeders;

use App\Models\ItemPriceProvince;
use App\Models\Province;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ItemPriceProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $itemPriceProvinces = Province::all()->map(function($province) {
            return [
                [
                    'item_price_id' => 'L.01',
                    'province_id' => $province->id,
                    'price' => 75000,
                    'created_at' => Carbon::now(),
                ],
                [
                    'item_price_id' => 'L.02',
                    'province_id' => $province->id,
                    'price' => 100000,
                    'created_at' => Carbon::now(),
                ],
            ];
        });

        ItemPriceProvince::insert($itemPriceProvinces->map(function($item) { return $item[0]; })->toArray());
        ItemPriceProvince::insert($itemPriceProvinces->map(function($item) { return $item[1]; })->toArray());
    }
}
