<?php

use App\Models\CustomAhp;
use App\Models\CustomAhs;
use App\Models\CustomAhsItem;
use App\Models\CustomItemPrice;
use App\Models\RabItem;

if (!function_exists('numToAlphabet')) {
    function numToAlphabet($number)
    {
        $letters = range('A', 'Z');
        return $letters[$number];
    }
}

if (!function_exists('determineCustomAhsItemName')) {
    function determineCustomAhsItemName(CustomAhsItem $customAhsItem)
    {
        switch ($customAhsItem->custom_ahs_itemable_type) {
            case CustomAhp::class :
            case CustomAhs::class :
                return $customAhsItem->name;
            break;
            case CustomItemPrice::class :
                return $customAhsItem->customAhsItemable->name;
            break;
        }
    }
}
