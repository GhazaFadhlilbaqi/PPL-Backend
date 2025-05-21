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
      $order->expired_at = self::calculateExpiredDate($order, $subscription, $order->created_at);
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
        'subscription_id' => $order->subscription_id
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
    $project->save();

    // Check for renew same package
    $pastOrder = Order::where('id', '!=', $order->id)
      ->where('status', 'completed')
      ->latest()
      ->first();

    // When latest order subscription id is same as current, it means that user extend their subscription.
    $latestExpiredDate = $pastOrder && $order->subscription_price_id == $pastOrder->subscription_price_id
      ? $pastOrder->expired_at
      : $order->expired_at;
    $order->expired_at = self::calculateExpiredDate($order, $subscription, $latestExpiredDate);
    $order->save();
  }

  private static function calculateExpiredDate(Order $order, Subscription $subscription, $expiredDate)
  {
    $extendedMonth = $order->subscriptionPrice->min_duration;
    if ($order->subscriptionPrice->duration_type === SubscriptionDurationType::YEARLY->value) {
      $extendedMonth = 12;
    }
    return Carbon::parse($expiredDate)->addMonths($extendedMonth)->toDateString();
  }
}
