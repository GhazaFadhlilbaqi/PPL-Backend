<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\CustomException;
use App\Models\Ahs;
use App\Models\AhsItem;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterAhsImportController implements WithMultipleSheets, SkipsEmptyRows
{
  private $masterAhsGroups;
  private $ahsItemTypes;

  public function __construct($masterAhsGroups, $ahsItemTypes)
  {
    $this->masterAhsGroups = $masterAhsGroups;
    $this->ahsItemTypes = $ahsItemTypes;
  }

  public function sheets(): array
  {
    return [
      new MasterAhsImportSheet($this->masterAhsGroups),
      new MasterAhsItemImportSheet($this->ahsItemTypes)
    ];
  }
}

class MasterAhsImportSheet implements ToCollection, WithChunkReading
{
  private $masterAhsGroups;

  public function __construct($masterAhsGroups)
  {
    $this->masterAhsGroups = $masterAhsGroups;
  }

  public function chunkSize(): int
  {
    return 500;
  }

  public function collection(Collection $rows)
  {
    // Remove table header
    $rows->shift();

    // Prepare masterAhsGroups map for fast lookup
    $referenceGroups = collect($this->masterAhsGroups)->pluck('key', 'title')->toArray();
    $insertData = [];
    foreach ($rows as $ahsRowData) {
      if ($ahsRowData->filter()->isEmpty()) continue;
      if (empty($ahsRowData[0])) continue;
      $groupKey = $referenceGroups[$ahsRowData[2]] ?? null;
      $insertData[] = [
        'code' => $ahsRowData[1],
        'name' => $ahsRowData[3],
        'groups' => $groupKey,
        'created_at' => now(),
        'updated_at' => now(),
      ];
    }

    // Batch insert at once
    if (empty($insertData)) return;
    foreach (array_chunk($insertData, 500) as $batch) {
      Ahs::upsert(
        $batch,
        ['code', 'groups'],
        ['name', 'updated_at']
      );
    }
  }
}

class MasterAhsItemImportSheet implements ToCollection, WithChunkReading
{
  private $ahsItemTypes;

  public function __construct($ahsItemTypes)
  {
    $this->ahsItemTypes = $ahsItemTypes;
  }

  public function chunkSize(): int
  {
    return 500;
  }

  public function collection(Collection $rows)
  {
    $rows->shift();

    AhsItem::truncate();

    $ahs_list = Ahs::all()->keyBy('code');
    $unit_names = Unit::pluck('id', 'name')
      ->mapWithKeys(function ($id, $name) {
          return [strtolower($name) => $id];
      })
      ->toArray();

    $ahs_items_data = [];
    foreach ($rows as $rowIndex => $rowData) {
      if ($rowData->filter()->isEmpty()) continue;
      $mutatedRowIndex = $rowIndex + 2;

      $ahs = $ahs_list[$rowData[1]] ?? null;
      if ($ahs === null) {
        throw new CustomException("Kode AHS " . $rowData[1] . " tidak ditemukan pada row: " . $mutatedRowIndex);
      };

      $ahs_reference = $ahs_list[$rowData[3]] ?? null;

      $ahs_name = $rowData[4] ?: ($ahs_reference->name ?? null);
      if ($ahs_name === null) {
        throw new CustomException("Nama ahs kosong pada row: " . $mutatedRowIndex);
      }

      $ahs_section = $this->ahsItemTypes->first(function ($ahsItemType) use ($rowData) {
        return strtolower($ahsItemType['title']) == strtolower($rowData[2]);
      });
      if ($ahs_section === null) {
        throw new CustomException("Kategori ahs " . $rowData[2] . " tidak ditemukan pada baris: " . $mutatedRowIndex);
      }

      if (!isset($rowData[3])) {
          throw new CustomException("Kode item AHS kosong pada baris: " . $mutatedRowIndex);
      }

      $unit_name = $unit_names[strtolower($rowData[5])] ?? null;
      if ($unit_name === null) {
        throw new CustomException("Satuan " . $rowData[5] . " tidak ditemukan pada baris: " . $mutatedRowIndex);
      }

      $coefficientData = str_replace(',', '.', $rowData[6]);
      if (!is_numeric($coefficientData)) {
          throw new CustomException("Coefficient tidak sesuai pada baris: " . $mutatedRowIndex);
      }

      $ahs_items_data[] =  [
        'ahs_id' => $ahs->id,
        'section' => $ahs_section['key'],
        'ahs_itemable_id' => $rowData[3],
        'ahs_itemable_type' => isset($ahs_reference)
          ? 'App\\Models\\Ahs'
          : 'App\\Models\\ItemPrice',
        'name' => $ahs_name,
        'unit_id' => $unit_name,
        'coefficient' => (float) $coefficientData,
        'created_at' => now(),
        'updated_at' => now(),
      ];
    }

    if (empty($ahs_items_data)) return;

    foreach (array_chunk($ahs_items_data, 500) as $batch) {
      AhsItem::insert($batch);
    }
  }
}
