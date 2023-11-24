<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Subscription::insert([
            [
                'id' => 'student',
                'name' => 'Student',
                'price' => 50000,
                'description' => '<li>Kustomisasi RAB</li><li>Akses full ke AHS permen PUPR 2016/2023 </li><li>Buat baru AHS / Custom AHS</li><li>Buat baru AHS / Custom AHS</li><li>Akses full ke AHP permen PUPR 2016/2023</li><li>Buat baru AHP/ Custom AHS</li><li>Kustomisasi Harga Satuan</li><li>Export RAB </li>',
                'subscription_type' => 'MONTHLY',
                'is_show' => true,
                'order' => 1,
                'promotion_price' => 200000,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 'starter',
                'name' => 'Starter',
                'price' => 100000,
                'description' => '<li>Kustomisasi RAB</li><li>Akses full ke AHS permen PUPR 2016/2023 </li><li>Buat baru AHS / Custom AHS</li><li>Buat baru AHS / Custom AHS</li><li>Akses full ke AHP permen PUPR 2016/2023</li><li>Buat baru AHP/ Custom AHS</li><li>Kustomisasi Harga Satuan</li><li>Export RAB </li>',
                'subscription_type' => 'QUARTERLY',
                'is_show' => true,
                'order' => 2,
                'promotion_price' => 600000,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 'demo',
                'name' => 'Demo',
                'price' => 0,
                'description' => '',
                'subscription_type' => 'DAILY',
                'is_show' => false,
                'order' => null,
                'promotion_price' => null,
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}
