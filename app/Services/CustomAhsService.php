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
  public function customFromMasterAhs(
    Project $project,
    Ahs $masterAhs,
    String $customCode,
    String $customName
  ) {
    return DB::transaction(function () use ($project, $masterAhs, $customCode, $customName) {
      $master_ahs = Ahs::where(['id' => $masterAhs->id])
        ->where(['reference_group_id' => $masterAhs->reference_group_id])
        ->first();
      if (!$master_ahs) {
        throw new CustomException('Data AHS tidak ditemukan');
      };
      return $this->createCustomAhs(
        $master_ahs,
        $project,
        $customCode,
        $customName
      );
    });
  }

  private function createCustomAhs(
    Ahs $masterAhs,
    Project $project,
    String $customCode,
    String $customName
  ) {
    $parentCustomAhs = CustomAhs::create([
      'code' => $customCode,
      'name' => $customName,
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
        $customAhsItemable = $this->createCustomAhs(
          $masterAhs,
          $project,
          $masterAhs->code,
          $masterAhs->name
        );
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
}
