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
        $this->call(RolePermissionSeeder::class);

        if (env('APP_ENV') === 'local') {
            $this->call(UserSeeder::class);
        }

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

        $this->call(FeatureSeeder::class);
        $this->call(SubscriptionFeatureSeeder::class);
    }
}
