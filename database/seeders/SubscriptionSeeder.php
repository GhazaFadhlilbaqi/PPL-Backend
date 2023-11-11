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
                'id' => 'starter',
                'name' => 'Starter',
                'price' => 10000,
                'description' => '<li>Testing</li><li>Testing</li><li>Testing</li>',
                'subscription_type' => 'MONTHLY',
                'is_show' => true,
                'order' => 1,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 'business',
                'name' => 'Business',
                'price' => 15000,
                'description' => '<li>Testing</li><li>Testing</li><li>Testing</li>',
                'subscription_type' => 'MONTHLY',
                'is_show' => true,
                'order' => 2,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 'professional',
                'name' => 'Professional',
                'price' => 20000,
                'description' => '<li>Testing</li><li>Testing</li><li>Testing</li>',
                'subscription_type' => 'MONTHLY',
                'is_show' => true,
                'order' => 3,
                'created_at' => Carbon::now(),
            ],
            [
                'id' => 'demo',
                'name' => 'Demo',
                'price' => 0,
                'description' => '<li>Testing</li><li>Testing</li><li>Testing</li>',
                'subscription_type' => 'DAILY',
                'is_show' => false,
                'order' => null,
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}
