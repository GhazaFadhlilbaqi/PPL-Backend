<?php

namespace Database\Seeders;

use App\Models\ItemPriceGroup;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ItemPriceGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $itemPriceGroups = collect([
            'UPAH TENAGA KERJA',
            'KELOMPOK TANAH, AIR, BATU, DAN SEMEN',
            'KELOMPOK KAYU',
            'KELOMPOK LOGAM',
            'KELOMPOK PERALATAN',
            'KELOMPOK ANALISA',
            'KELOMPOK LAIN - LAIN',
        ]);

        ItemPriceGroup::insert($itemPriceGroups->map(function($itemPriceName) {
            return [
                'name' => $itemPriceName,
                'created_at' => Carbon::now()
            ];
        })->toArray());
    }
}
