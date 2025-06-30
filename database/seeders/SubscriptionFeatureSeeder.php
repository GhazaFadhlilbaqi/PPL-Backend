<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionFeatureSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $mapping = [
      'demo' => [
        'RAB_TEMPLATE',
        'ITEM_PRICE_REFERENCE',
        'AHSP_PUPR_2024_REFERENCE'
      ],
      'student' => [
        'RAB_TEMPLATE',
        'ITEM_PRICE_REFERENCE',
        'AHSP_PUPR_2024_REFERENCE',
        'RAB_EXCEL_EXPORT',
      ],
      'starter' => [
        'RAB_TEMPLATE',
        'ITEM_PRICE_REFERENCE',
        'AHSP_PUPR_2024_REFERENCE',
        'RAB_EXCEL_EXPORT',
        'SIMPLE_RAB_REFERENCE',
        'CREATE_IMPLEMENTATION_SCHEDULE',
      ],
      'professional' => [
        'RAB_TEMPLATE',
        'ITEM_PRICE_REFERENCE',
        'AHSP_PUPR_2024_REFERENCE',
        'RAB_EXCEL_EXPORT',
        'SIMPLE_RAB_REFERENCE',
        'CREATE_IMPLEMENTATION_SCHEDULE',
        'CREATE_AUTOMATED_SCURVE',
        'CALCULATE_HUMAN_RESOURCE_NEEDS',
        'CALCULATE_MATERIAL_NEEDS',
        'CALCULATE_TOOLS_NEEDS',
        'IMPORT_CUSTOM_RAB_EXCEL',
        'PERSONAL_SUPPORT_TEAM',
      ],
    ];
    foreach ($mapping as $subscriptionCode => $featureCodes) {
      $subscription = Subscription::where('id', $subscriptionCode)->first();
      if (!$subscription) continue;
      $featureIds = Feature::whereIn('code', $featureCodes)->pluck('id')->toArray();
      $subscription->features()->syncWithoutDetaching($featureIds);
    }
  }
}
