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
                'monthly_price' => 38000,
                'yearly_price' => 33000,
                'min_month' => 6,
                'description' => 'Teman setia mahasiswa buat belajar dan latihan menyusun RAB',
                'is_show' => true,
                'order' => 1,
                'promotion_price' => 200000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'starter',
                'name' => 'Starter',
                'monthly_price' => 59000,
                'yearly_price' => 50000,
                'min_month' => 3,
                'description' => 'Solusi Tepat untuk Kamu, Konsultan atau Perangkat Desa, agar hitung RAB jadi lebih mudah',
                'is_show' => true,
                'order' => 2,
                'promotion_price' => 600000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'professional',
                'name' => 'Professional',
                'monthly_price' => 499000,
                'yearly_price' => 250000,
                'min_month' => 1,
                'description' => 'Dirancang Khusus untuk kontraktor yang ingin lebih siap dan percaya diri memenangkan tender di LPSE',
                'is_show' => true,
                'order' => 3,
                'promotion_price' => 1000000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'demo',
                'name' => 'Demo',
                'price' => 0,
                'description' => '',
                'is_show' => false,
                'order' => null,
                'promotion_price' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
    }
}
