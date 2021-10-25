<?php

namespace Database\Seeders;

use App\Models\ItemPrice;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            ProvinceSeeder::class,
            UnitSeeder::class,
            ItemPriceGroupSeeder::class,
            ItemPriceSeeder::class,
            ItemPriceProvinceSeeder::class,
        ]);
    }
}
