<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Ahs;
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
  public function customFromMasterAhs(Project $project, int $master_ahs_id, string $group_key)
  {
    return DB::transaction(function () use ($project, $master_ahs_id, $group_key) {
      // 1. Find master ahs based on id & group reference
      $master_ahs = Ahs::where(['id' => $master_ahs_id])
        ->where(['groups' => $group_key])
        ->first();
      if (!$master_ahs) {
        throw new CustomException('Data AHS tidak ditemukan');
      };

      // 2. Check past custom ahs related to master ahs
      $custom_ahs = CustomAhs::where(['code' => $master_ahs->code])
        ->where(['project_id' => $project->id])
        ->first();
      if ($custom_ahs) return $custom_ahs;
      $custom_ahs = CustomAhs::create([
        'code' => $master_ahs->code,
        'project_id' => $project->id,
        'name' => $master_ahs->name
      ]);

      // 3. Create custom item price group when not exists
      $master_item_price_groups = $this->getMasterItemPriceGroups($master_ahs, $project->province_id);
      Log::info(json_encode($master_item_price_groups));
      foreach ($master_item_price_groups as $master_item_price_group) {
        $customGroup = CustomItemPriceGroup::firstOrCreate(
            [
              'project_id' => $project->id,
              'master_item_price_group_id' => $master_item_price_group->id,
            ],
            ['name' => $master_item_price_group->name]
        );

        // 4. Create custom item price
        $master_item_prices = $master_item_price_group->itemPrice;
        foreach ($master_item_prices as $master_item_price) {
          $is_exists = CustomItemPrice::where('project_id', $project->id)
            ->where('code', $master_item_price->code)
            ->exists();
          if ($is_exists) continue;
          $price_by_province = $master_item_price->price
            ->filter(function ($price_by_province) use ($project) {
              return $price_by_province->province_id === $project->province_id;
            })
            ->values()
            ->first();
          CustomItemPrice::create([
            'code' => $master_item_price->id,
            'custom_item_price_group_id' => $customGroup->id,
            'unit_id' => $master_item_price->unit_id,
            'project_id' => $project->id,
            'name' => $master_item_price->name,
            'is_default' => true,
            'price' => $price_by_province ? $price_by_province->price : 0,
            'default_price' => $price_by_province ? $price_by_province->price : 0
          ]);
        }
      }

      // 5. Create AHS items (no need to check for existing custom AHS items, as the process is skipped if custom AHS already exists)
      $master_ahs_items = $master_ahs->ahsItem;
      foreach ($master_ahs_items as $master_ahs_item) {
        if ($master_ahs_item->ahs_itemable_type == ItemPrice::class) {
          $custom_ahs_itemable_id = CustomItemPrice::where([
            ['code', '=', $master_ahs_item->ahs_itemable_id],
            ['project_id', '=', $project->id]
          ])->first()?->id;
          $custom_ahs_itemable_type = CustomItemPrice::class;
        } else {
          $customAhs = CustomAhs::firstOrCreate(
            [
              'code' => $master_ahs_item->ahs_itemable_id,
              'project_id' => $project->id,
            ],
            ['name' => $master_ahs_item->ahsItemable?->name ?? '-']
          );
          $custom_ahs_itemable_id = $customAhs->id;
          $custom_ahs_itemable_type = CustomAhs::class;
        }
        if (!$custom_ahs_itemable_id) {
          throw new CustomException("Ahs tidak ditemukan!");
        }
        CustomAhsItem::create([
          'custom_ahs_id' => $custom_ahs->id,
          'unit_id' => $master_ahs_item->unit_id,
          'coefficient' => $master_ahs_item->coefficient,
          'section' => $master_ahs_item->section,
          'custom_ahs_itemable_id' => $custom_ahs_itemable_id,
          'custom_ahs_itemable_type' => $custom_ahs_itemable_type
        ]);
      }

      return $custom_ahs;
    });
  }

  public function calculateCustomAhsPrice(int $profit_margin, CustomAhs $custom_ahs)
  {
    $custom_ahs_price = 0;
    foreach ($custom_ahs->customAhsItem as $customAhsItem) {
      $ahs_item_price = ($customAhsItem->customAhsItemable->price * $customAhsItem->coefficient);
      $custom_ahs_price = $custom_ahs_price + ($ahs_item_price + ($ahs_item_price * ($profit_margin / 100)));
    }
    return $custom_ahs_price;
  }

  private function getMasterItemPriceGroups(Ahs $master_ahs)
  {
    $item_price_ids = $master_ahs->ahsItem()
      ->where('ahs_itemable_type', ItemPrice::class)
      ->pluck('ahs_itemable_id')
      ->toArray();
    Log::info(json_encode($item_price_ids));
    if (empty($item_price_ids)) return collect();

    $itemPriceGroups = ItemPriceGroup::whereHas('itemPrice', function ($query) use ($item_price_ids) {
      $query->whereIn('id', $item_price_ids);
      Log::info('Matching item_price_ids', $item_price_ids);
    })->get();
    Log::info(json_encode($itemPriceGroups));

    $itemPrices = ItemPrice::whereIn('id', $item_price_ids)
      ->with('itemPriceGroup')
      ->get();
    Log::info($itemPrices);

    return ItemPriceGroup::whereHas('itemPrice', function ($query) use ($item_price_ids) {
      $query->whereIn('id', $item_price_ids);
    })->distinct()->get();
  }
}
