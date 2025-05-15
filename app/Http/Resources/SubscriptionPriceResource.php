<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'subscriptionId' => $this->subscription_id,
            'durationType' => $this->duration_type,
            'price' => $this->price,
            'discountedPrice' => $this->discounted_price,
            'minDuration' => $this->min_duration
        ];
    }
}
