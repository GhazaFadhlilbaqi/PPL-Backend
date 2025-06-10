<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Ahs;
use App\Models\AhsItem;
use App\Models\CustomAhs;
use App\Models\CustomAhsItem;
use App\Models\CustomItemPrice;
use App\Models\CustomItemPriceGroup;
use App\Models\ItemPrice;
use App\Models\ItemPriceGroup;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomAhsService
{
  public function customFromMasterAhs(Project $project, int $master_ahs_id, string $referenceGroupId)
  {
    return DB::transaction(function () use ($project, $master_ahs_id, $referenceGroupId) {
      $master_ahs = Ahs::where(['id' => $master_ahs_id])
        ->where(['reference_group_id' => $referenceGroupId])
        ->first();
      if (!$master_ahs) {
        throw new CustomException('Data AHS tidak ditemukan');
      };
      $customAhs = $this->createCustomAhs($master_ahs, $project);
      return $customAhs;
    });
  }

  private function createCustomAhs($masterAhs, $project)
  {
    $parentCustomAhs = CustomAhs::create([
      'code' => $masterAhs->code,
      'name' => $masterAhs->name,
      'project_id' => $project->id,
    ]);

    $masterAhsItems = AhsItem::where('ahs_id', $masterAhs->id)->get();
    foreach ($masterAhsItems as $masterAhsItem) {
      if (!$masterAhsItem->ahsItemable) return;
      
      $customAhsItemable = null;
      $customAhsItemableType = null;

      if ($masterAhsItem->ahs_itemable_type == ItemPrice::class) {
        $masterItemPrice = $masterAhsItem->ahsItemable;
        $customItemPriceGroup = CustomItemPriceGroup::firstOrCreate(
          [
            'project_id' => $project->id,
            'name' => $masterItemPrice->itemPriceGroup->name
          ]
        );
        $masterItemPriceByProvince = $masterItemPrice->price
          ->where('province_id', $project->province_id)
          ->first();
        $customAhsItemable = CustomItemPrice::create([
          'code' => $masterItemPrice->id,
          'custom_item_price_group_id' => $customItemPriceGroup->id,
          'unit_id' => $masterItemPrice->unit_id,
          'project_id' => $project->id,
          'name' => $masterItemPrice->name,
          'price' => $masterItemPriceByProvince->price ?? 0
        ]);
        $customAhsItemableType = CustomItemPrice::class;
      }

      if ($masterAhsItem->ahs_itemable_type == Ahs::class) {
        $masterAhs = $masterAhsItem->ahsItemable;
        $customAhsItemable = $this->createCustomAhs($masterAhs, $project);
        $customAhsItemableType = CustomAhs::class;
      }

      CustomAhsItem::create([
        'name' => $customAhsItemableType === CustomAhs::class
          ? $customAhsItemable->name
          : null,
        'custom_ahs_id' => $parentCustomAhs->id,
        'unit_id' => $masterAhsItem->unit_id,
        'coefficient' => $masterAhsItem->coefficient,
        'section' => $masterAhsItem->section,
        'custom_ahs_itemable_id' => $customAhsItemable->id,
        'custom_ahs_itemable_type' => $customAhsItemableType
      ]);
    }
  
    return $parentCustomAhs;
  }

  public function calculateCustomAhsPrice(int $profit_margin, CustomAhs $custom_ahs)
  {
    $custom_ahs_price = 0;
    foreach ($custom_ahs->customAhsItem as $customAhsItem) {
      $subtotalPrice = $customAhsItem->custom_ahs_itemable_type == CustomAhs::class
        ? $this->calculateCustomAhsPrice($profit_margin, $customAhsItem->customAhsItemable)
        : $customAhsItem->customAhsItemable->price;
      $ahs_item_price = $subtotalPrice * $customAhsItem->coefficient;
      if ($customAhsItem->custom_ahs_itemable_type == CustomAhs::class) {
        $custom_ahs_price = $custom_ahs_price + $ahs_item_price;
        continue;
      }
      if ($customAhsItem->custom_ahs_itemable_type == CustomItemPrice::class) {
        $custom_ahs_price = $custom_ahs_price + ($ahs_item_price + ($ahs_item_price * ($profit_margin / 100)));
        continue;
      }
    }
    return $custom_ahs_price;
  }

  private function getMasterItemPriceGroups(Ahs $master_ahs)
  {
    $item_price_ids = $master_ahs->ahsItem()
      ->where('ahs_itemable_type', ItemPrice::class)
      ->pluck('ahs_itemable_id')
      ->toArray();
    if (empty($item_price_ids)) return collect();
    return ItemPriceGroup::whereHas('itemPrice', function ($query) use ($item_price_ids) {
      $query->whereIn('id', $item_price_ids);
    })->distinct()->get();
  }
}
