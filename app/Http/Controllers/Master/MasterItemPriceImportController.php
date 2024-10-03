<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\CustomException;
use App\Models\ItemPrice;
use App\Models\ItemPriceGroup;
use App\Models\ItemPriceProvince;
use App\Models\Province;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Vinkla\Hashids\Facades\Hashids;

class MasterItemPriceImportController implements ToCollection {
  private Collection $itemPriceRows;

  public function collection(Collection $rows) {
      $this->itemPriceRows = $rows;
      // 1) Remove table header
      $this->itemPriceRows->shift();
      $provinceHeaders = $this->itemPriceRows->shift();

      // 2) Fetch provinces for update item price based on provinces
      $provinces = Province::all();

      // 3) Update item price when item price data is not exists in database
      $itemPrices = ItemPrice::all();
      foreach ($itemPrices as $itemPrice) {
         // 3.1) Remove item price data when data is not found in excel
        $itemPriceRow = $this->itemPriceRows->first(function($row) use ($itemPrice){
          return $row[2] == $itemPrice->id;
        });
        if (!$itemPriceRow) {
          $itemPrice->delete();
          continue;
        }

        // 3.2) Check item price group availability
        $itemPriceGroupId = $this->checkItemPriceGroupValidity($this->itemPriceRows, $itemPriceRow[2], $itemPriceRow[1]);

        // 3.3) Check unit id availability
        $unitId = $this->checkUnitValidity($this->itemPriceRows, $itemPriceRow[2],$itemPriceRow[4]);

        // 3.4) Update item price data with updated one from excel
        $itemPrice->update([
          'item_price_group_id' => $itemPriceGroupId,
          'name' => $itemPriceRow[3],
          'unit_id' => $unitId
        ]);
        $this->updateItemPriceProvince($provinces, $provinceHeaders, $itemPrice, $itemPriceRow);

        // 3.5) Exclude item price data from excel for create new data flow
        $this->itemPriceRows = $this->itemPriceRows->filter(function($row) use ($itemPriceRow) {
          return $row[2] != $itemPriceRow[2];
        })->values();
      }

      // 4) Create item price when item price data is not exists on database
      foreach ($this->itemPriceRows as $row) {
        // 4.1) Check item price group availability
        $itemPriceGroupId = $this->checkItemPriceGroupValidity($this->itemPriceRows, $row[2],$row[1]);

        // 4.2) Check unit id availability
        $unitId = $this->checkUnitValidity($this->itemPriceRows, $row[2], $row[4]);

        // 4.3) Create item price data
        $itemPrice = ItemPrice::create([
          'id' => $row[2],
          'item_price_group_id' => $itemPriceGroupId,
          'name' => $row[3],
          'unit_id' => $unitId
        ]);
        $this->updateItemPriceProvince($provinces, $provinceHeaders, $itemPrice, $row);
      }
  }

  private function checkItemPriceGroupValidity(Collection &$itemRows, String $itemPriceId, String $groupName) {
    $itemPriceGroupId = ItemPriceGroup::where(['name' => $groupName])->pluck('id')->first();
    if ($itemPriceGroupId) {
      return $itemPriceGroupId;
    }
    $itemRows = $itemRows->filter(function($originalRow) use ($groupName) {
      return $originalRow[1] != $groupName;
    })->values();
    throw new CustomException('Cannot input '.$itemPriceId.' because Item Group not found.');
  }

  private function checkUnitValidity(Collection &$itemRows, String $itemPriceId, String $unitName) {
    $unitId = Unit::where(['name' => $unitName])->pluck('id')->first();
    if ($unitId) {
      return $unitId;
    }
    $itemRows = $itemRows->filter(function($originalRow) use ($unitName) {
      return $originalRow[4] != $unitName;
    })->values();
    throw new CustomException('Cannot input '.$itemPriceId.' because Unit not found.');
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