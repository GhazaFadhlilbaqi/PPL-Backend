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
                'description' => '
                    <ul class="pl-0">
                    <li>Kustomisasi RAB</li>
                    <li>Buat baru AHS / Custom AHS</li>
                    <li>Akses full ke AHS permen PUPR 2016/2023</li>
                    <li>Akses full ke AHP permen PUPR 2016/2023 </li>
                    <li>Kustomisasi Harga Satuan</li>
                    <li>Export RAB (Excel) </li>
                    </ul>',
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
                'description' => '<ul class="pl-0"><li>Kustomisasi RAB</li>
                <li>Buat baru AHS / Custom AHS</li>
                <li>Akses full ke AHS permen PUPR 2016/2023</li>
                <li>Akses full ke AHP permen PUPR 2016/2023 </li>
                <li>Kustomisasi Harga Satuan</li>
                <li>Export RAB (Excel) </li></ul>',
                'subscription_type' => 'QUARTERLY',
                'is_show' => true,
                'order' => 2,
                'promotion_price' => 600000,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 'professional',
                'name' => 'Professional',
                'price' => 500000,
                'description' => '<ul class="pl-0"><li>Kustomisasi RAB</li>
                    <li>Buat baru AHS / Custom AHS</li>
                    <li>Akses full ke AHS permen PUPR 2016/2023</li>
                    <li>Akses full ke AHP permen PUPR 2016/2023 </li>
                    <li>Kustomisasi Harga Satuan</li>
                    <li>Export RAB (Excel) </li>
                    <li>Kustomisasi Jadwal Pelaksanaan</li>
                    <li>Export Kurva S</li>
                </ul>
                ',
                'subscription_type' => 'MONTHLY',
                'is_show' => true,
                'order' => 3,
                'promotion_price' => 1000000,
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
