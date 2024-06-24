<?php

namespace App\Http\Controllers\Master;

use App\Models\Ahs;
use App\Models\AhsItem;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterAhsImportController implements WithMultipleSheets {
  private $masterAhsGroups;
  private $ahsItemTypes;

  public function __construct($masterAhsGroups, $ahsItemTypes) {
    $this->masterAhsGroups = $masterAhsGroups;
    $this->ahsItemTypes = $ahsItemTypes;
  }

  public function sheets(): array {
    return [
        new MasterAhsImportSheet($this->masterAhsGroups),
        new MasterAhsItemImportSheet($this->ahsItemTypes)
    ];
  }
}

class MasterAhsImportSheet implements ToCollection {
  private $masterAhsGroups;

  public function __construct($masterAhsGroups) {
    $this->masterAhsGroups = $masterAhsGroups;
  }

  public function collection(Collection $rows) {
      // Remove table header
      $rows->shift();

      // Get all ahs datas
      $ahsList = Ahs::all();

      // Create new ahs when ahs code from excel is not found on databasse
      foreach ($rows as $ahsRow) {
        $ahs = $ahsList->first(function($ahs) use ($ahsRow) {
          return $ahs->id == $ahsRow[1];
        });
        if ($ahs) { continue; }
        Ahs::create([
          'id' => $ahsRow[1],
          'groups' => $this->masterAhsGroups->first(function($masterAhsGroup) use ($ahsRow) {
            return $masterAhsGroup['title'] == $ahsRow[2];
          })['key'],
          'name' => $ahsRow[3]
        ]);
        $rows = $rows->filter(function($row) use ($ahsRow) {
          return $row[1] != $ahsRow[1];
        });
      }

      // Update existing ahs (when id found) or remove when id from database is not found excel
      foreach ($ahsList as $ahs) {
        $row = $rows->first(function($row) use ($ahs) {
          return $ahs->id == $row[1];
        });
        if ($row) {
          $ahs->update(['name' => $row[3]]);
          continue;
        }
        $ahs->delete();
      }
  }
}

class MasterAhsItemImportSheet implements ToCollection {
  private $ahsItemTypes;

  public function __construct($ahsItemTypes) {
    $this->ahsItemTypes = $ahsItemTypes;
  }
  
  public function collection(Collection $rows) {
      // Remove table header
      $rows->shift();

      // Get all ahs datas
      $ahsList = Ahs::all();

      // Clear ahs list item first
      foreach ($ahsList as $ahs) {
        AhsItem::where('ahs_id', $ahs->id)->delete();
      }
  
      // Re-add all ahs item
      foreach ($rows as $row) {
        $isAhsReference = $ahsList->contains('id', $row[3]);
        if (!$ahsList->contains('id', $row[1])) {
          continue;
        }
        $ahsName = null; 
        if ($isAhsReference) {
          $ahsName = !$row[4]
            ? $ahsList->first(function($ahs) use ($row) {
              return $ahs->id == $row[3];
            })->name
            : $row[4]; 
        }
        AhsItem::create([
          'ahs_id' => $row[1],
          'section' => $this->ahsItemTypes->first(function($ahsItemType) use ($row) {
            return $ahsItemType['title'] == $row[2];
          })['key'],
          'ahs_itemable_id' => $row[3],
          'ahs_itemable_type' => $isAhsReference
            ? 'App\\Models\\Ahs'
            : 'App\\Models\\ItemPrice',
          'name' => $ahsName,
          'unit_id' => $isAhsReference
            ? Unit::where(['name' => $row[5]])->pluck('id')->first()
            : null,
          'coefficient' => $row[6],
        ]);
      }
  }
}
