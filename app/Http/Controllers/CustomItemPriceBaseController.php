<?php

namespace App\Http\Controllers;

use App\Models\CustomAhs;
use App\Models\CustomItemPrice;

class CustomItemPriceBaseController extends Controller
{
    protected function getCustomItemPriceDependencies($projectId, $customItemPriceId)
    {
        $ahs = CustomAhs::where('project_id', $projectId)->whereHas('customAhsItem', function($q) use ($customItemPriceId) {
            $q->where('custom_ahs_itemable_type', CustomItemPrice::class)->where('custom_ahs_itemable_id', $customItemPriceId);
        })->get();

        return [
            'ahs' => $ahs
        ];
    }
}
