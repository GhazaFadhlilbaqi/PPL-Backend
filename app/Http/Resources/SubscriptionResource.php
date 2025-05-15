<?php

namespace App\Http\Resources;

use App\Models\SubscriptionPrice;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'name' => $this->name,
            'order' => $this->order,
            'monthlyPrice' => $this->monthly_price,
            'yearlyPrice' => $this->yearly_price,
            'minMonth' => $this->min_month,
            'description' => $this->description,
            'features' => FeatureResource::collection($this->features),
            'prices' => SubscriptionPriceResource::collection($this->prices)
        ];
    }
}
