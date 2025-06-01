<?php

namespace App\Services;

use App\Enums\OrderType;
use App\Enums\SubscriptionDurationType;
use App\Models\Order;
use App\Models\SubscriptionPrice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class OrderService {
  public function createOrder($params, SubscriptionPrice $subscriptionPrice) {
    $validatedParams = Validator::make($params, [
        'user_id'    => 'required|exists:users,id',
        'type'       => ['required', new Enum(OrderType::class)],
        'project_id' => 'exists:projects,id',
    ])->validate();
    $calculatedGrossMount = $subscriptionPrice->duration_type === SubscriptionDurationType::YEARLY->value
        ? $subscriptionPrice->discounted_price * 12
        : $subscriptionPrice->discounted_price * $subscriptionPrice->min_duration;
    $order = Order::create([
        'order_id'              => $this->generateOrderId(),
        'user_id'               => $validatedParams['user_id'],
        'project_id'            => $validatedParams['project_id'] ?? null,
        'subscription_id'       => $subscriptionPrice->subscription_id,
        'subscription_price_id' => $subscriptionPrice->id,
        'status'                => 'waiting_for_payment',
        'gross_amount'          => $calculatedGrossMount,
        'type'                  => $validatedParams['type']
    ]);
    return $order;
  }

  private function generateOrderId()
  {
      return strtoupper(Str::random(16));
  }
}