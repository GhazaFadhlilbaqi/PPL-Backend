<?php

namespace Database\Seeders;

use App\Models\ItemPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        # Some seeders are commented because it's populated from /database/sample-data
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
        ]);

        if (env('USE_AHS_POPULATE', false)){
            Artisan::call('populate:ahs');
        } else {
            $this->call([
                ProvinceSeeder::class,
                UnitSeeder::class,
                ItemPriceGroupSeeder::class,
                ItemPriceSeeder::class,
                ItemPriceProvinceSeeder::class,
            ]);
        }

        $this->call([
            SubscriptionSeeder::class,
            MasterRabCategorySeeder::class,
        ]);
    }
}
