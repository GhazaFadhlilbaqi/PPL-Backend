<?php

namespace Database\Seeders;

use App\Models\ItemPrice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ItemPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ItemPrice::insert([
            [
                'id' => 'L.01',
                'item_price_group_id' => 1,
                'unit_id' => 1,
                'name' => 'Pekerja',
                'created_at' => Carbon::now()
            ],
            [
                'id' => 'L.02',
                'item_price_group_id' => 1,
                'unit_id' => 1,
                'name' => 'Mandor',
                'created_at' => Carbon::now()
            ],
        ]);
    }
}
