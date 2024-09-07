<?php
  namespace App\Helpers;

use App\Models\Subscription;
use Carbon\Carbon;

  class ProjectHelper {
      public static function get_expired_date($subscription_id) {
          $subscription = Subscription::find($subscription_id);
          $expiredDates = [
            'THREEDAYS' => Carbon::now()->subDay(-3),
            'MONTHLY' => Carbon::now()->subDay(-3),
            'ANNUALLY' => Carbon::now()->subYear(-1)
          ];
          if (!array_key_exists($subscription->subscription_type, $expiredDates)) {
            return Carbon::now()->subDay(-1);
          }
          return $expiredDates[$subscription->subscription_type];
      }
  }
?>