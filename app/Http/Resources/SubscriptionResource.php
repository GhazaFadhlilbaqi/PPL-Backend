<?php

namespace App\Http\Resources;

use App\Enums\SubscriptionType;
use App\Helpers\EmailHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        $isAvailable = true;
        if ($this->id == SubscriptionType::STUDENT->value) {
            $isAvailable = EmailHelper::isStudentEmail(Auth::user()->email);
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'order' => $this->order,
            'monthlyPrice' => $this->monthly_price,
            'yearlyPrice' => $this->yearly_price,
            'minMonth' => $this->min_month,
            'description' => $this->description,
            'features' => FeatureResource::collection($this->features),
            'prices' => SubscriptionPriceResource::collection($this->prices),
            'isAvailable' => $isAvailable
        ];
    }
}
