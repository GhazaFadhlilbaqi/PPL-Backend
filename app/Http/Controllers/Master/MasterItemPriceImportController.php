<?php

namespace App\Http\Controllers\Master;

use App\Models\ItemPrice;
use App\Models\ItemPriceGroup;
use App\Models\ItemPriceProvince;
use App\Models\Province;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Vinkla\Hashids\Facades\Hashids;

class MasterItemPriceImportController implements ToCollection {
  public function collection(Collection $rows) {
      // Remove table header
      $rows->shift();
      $provinceHeaders = $rows->shift();
      $provinces = Province::all();

      $itemPrices = ItemPrice::all();
      foreach ($itemPrices as $itemPrice) {
        $itemPriceRow = $rows->first(function($row) use ($itemPrice){
          return $row[2] == $itemPrice->id;
        });

        if (!$itemPriceRow) {
          $itemPrice->delete();
          continue;
        }

        $itemPrice->update([
          'item_price_group_id' => ItemPriceGroup::where(['name' => $itemPriceRow[1]])->pluck('id')->first(),
          'name' => $itemPriceRow[3],
          'unit_id' => Unit::where(['name' => $itemPriceRow[4]])->pluck('id')->first()
        ]);
        $this->updateItemPriceProvince($provinces, $provinceHeaders, $itemPrice, $itemPriceRow);

        $rows = $rows->filter(function($row) use ($itemPriceRow) {
          return $row[2] != $itemPriceRow[2];
        })->values();
      }

      foreach ($rows as $row) {
        $itemPrice = ItemPrice::create([
          'id' => $row[2],
          'item_price_group_id' => ItemPriceGroup::where(['name' => $row[1]])->pluck('id')->first(),
          'name' => $row[3],
          'unit_id' => Unit::where(['name' => $row[4]])->pluck('id')->first()
        ]);
        $this->updateItemPriceProvince($provinces, $provinceHeaders, $itemPrice, $row);
      }
  }

  private function updateItemPriceProvince($provinces, $provinceHeaders, $itemPrice, $row) {
    foreach ($provinceHeaders as $index => $provinceHeader) {
      if ($provinceHeader == null) { continue; }
      $province = $provinces->first(function($province) use ($provinceHeader) {
        return $province->name == strtoupper($provinceHeader);
      });
      $itemPriceProvince = ItemPriceProvince::where('province_id', Hashids::decode($province->hashId)[0])
        ->where('item_price_id', $itemPrice->id)
        ->first();
      if ($itemPriceProvince) {
        $itemPriceProvince->update(['price' => $row[$index]]);
        continue;
      };
      if ($row[$index] == null) { continue; }
      ItemPriceProvince::create([
        'province_id' => Hashids::decode($province->hashId)[0],
        'item_price_id' => $itemPrice->id,
        'price' => $row[$index]
      ]);
    }
  }
}