<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Subscription;
use App\Models\SubscriptionPrice;
use Illuminate\Database\Seeder;

class SubscriptionPriceSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $subscriptionPrices = [
      'student' => [
        [
          'duration_type' => 'MONTHLY',
          'price' => 150000,
          'discounted_price' => 49000,
          'min_duration' => 3,
          'is_active' => true
        ],
        [
          'duration_type' => 'YEARLY',
          'price' => 100000,
          'discounted_price' => 33000,
          'min_duration' => 1,
          'is_active' => true
        ]
      ],
      'starter' => [
        [
          'duration_type' => 'MONTHLY',
          'price' => 200000,
          'discounted_price' => 100000,
          'min_duration' => 1,
          'is_active' => true
        ],
        [
          'duration_type' => 'MONTHLY',
          'price' => 100000,
          'discounted_price' => 59000,
          'min_duration' => 3,
          'is_active' => true
        ],
        [
          'duration_type' => 'YEARLY',
          'price' => 200000,
          'discounted_price' => 50000,
          'min_duration' => 1,
          'is_active' => true
        ]
      ],
      'professional' => [
        [
          'duration_type' => 'MONTHLY',
          'price' => 1000000,
          'discounted_price' => 499000,
          'min_duration' => 1,
          'is_active' => true
        ],
        [
          'duration_type' => 'MONTHLY',
          'price' => 499000,
          'discounted_price' => 333000,
          'min_duration' => 3,
          'is_active' => true
        ],
        [
          'duration_type' => 'YEARLY',
          'price' => 899000,
          'discounted_price' => 249000,
          'min_duration' => 1,
          'is_active' => true
        ]
      ],
      'demo' => [
        [
          'duration_type' => 'MONTHLY',
          'price' => 0,
          'discounted_price' => 0,
          'min_duration' => 1,
          'is_active' => true
        ]
      ]
    ];

    foreach ($subscriptionPrices as $subscriptionId => $priceDatas) {
      foreach ($priceDatas as $priceData) {
        SubscriptionPrice::create([
          'subscription_id' => $subscriptionId,
          'duration_type' => $priceData['duration_type'],
          'price' => $priceData['price'],
          'discounted_price' => $priceData['discounted_price'],
          'min_duration' => $priceData['min_duration'],
          'is_active' => $priceData['is_active'],
        ]);
      }
    }
  }
}
