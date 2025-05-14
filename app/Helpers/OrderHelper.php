<?php

namespace App\Helpers;

use App\Enums\SubscriptionDurationType;
use App\Models\Order;
use App\Models\Project;
use App\Models\Subscription;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderHelper
{
  public static function setOrderAsSuccessful(Order $order)
  {
    $project = $order->type == 'create'
      ? self::storeProject($order)
      : Project::find($order->project_id);
    $subscription = Subscription::where('id', $order->subscription_id)->first();

    if ($order->type == 'create') {
      $order->expired_at = self::calculateExpiredDate($project, $subscription, $order->created_at);
    } else if ($order->type == 'renew') {
      self::updateRenewProject($project, $order, $subscription);
    }

    $order->project_id = $project->id;
    $order->status = 'completed';
    $order->is_active = true;
    $order->save();

    // Remove all past orders, when current order paid
    if ($order) {
      Order::where('project_id', $project->id)
        ->where('created_at', '<', $order->created_at)
        ->delete();
    }
  }

  private static function storeProject($order)
  {
    try {
      DB::beginTransaction();

      $projectTemporary = $order->projectTemporary;

      $project = Project::create([
        'user_id' => $projectTemporary->user_id,
        'name' => $projectTemporary->name,
        'activity' => $projectTemporary->activity,
        'job' => $projectTemporary->job,
        'address' => $projectTemporary->address,
        'province_id' => $projectTemporary->province_id,
        'fiscal_year' => $projectTemporary->fiscal_year,
        'profit_margin' => $projectTemporary->profit_margin,
        'ppn' => $projectTemporary->ppn,
        'subscription_id' => $order->subscription_id,
        'subscription_duration_type' => $order->subscription_duration_type,
      ]);

      $projectTemporary->delete();

      DB::commit();

      return $project;
    } catch (Exception $e) {
      DB::rollBack();
      return response()->json([
        'message' => $e->getMessage(),
      ]);
    }
  }

  private static function updateRenewProject($project, $order, $subscription)
  {
    // Update subscription id
    $project->subscription_id = $order->subscription_id;
    $project->subscription_duration_type = $order->subscription_duration_type;
    if ($project->subscription_duration_type === null) {
      $project->subscription_duration_type = SubscriptionDurationType::MONTHLY->value;
    }
    $project->save();

    // Check for renew same package
    $latestOrder = Order::where('id', '!=', $order->id)
      ->where('status', 'completed')
      ->latest()
      ->first();

    // When latest order subscription id is same as current, it means that user extend their subscription.
    $latestExpiredDate = $latestOrder && $order->subscription_id == $latestOrder->id
      ? $latestOrder->expired_at
      : $order->expired_at;
    $order->expired_at = self::calculateExpiredDate($project, $subscription, $latestExpiredDate);
    $order->save();
  }

  private static function calculateExpiredDate(Project $project, Subscription $subscription, $expiredDate)
  {
    $extendedMonth = 0;
    if ($project->subscription_duration_type === SubscriptionDurationType::YEARLY->value) {
      $extendedMonth = 12;
    } else if ($project->subscription_duration_type === SubscriptionDurationType::MONTHLY->value) {
      $extendedMonth = $subscription->min_month;
    }
    return Carbon::parse($expiredDate)->addMonths($extendedMonth)->toDateString();
  }
}
