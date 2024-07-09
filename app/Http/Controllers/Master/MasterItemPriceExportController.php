<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\CountableItemController;
use App\Models\ItemPrice;
use App\Models\ItemPriceGroup;
use App\Models\ItemPriceProvince;
use App\Models\Province;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterItemPriceExportController extends CountableItemController implements FromCollection, WithEvents, WithColumnWidths, WithColumnFormatting, WithDefaultStyles, WithStyles {
  private $headerStyle = [
    'alignment' => [
      'horizontal' => Alignment::HORIZONTAL_CENTER
    ],
    'fill' => [
      'fillType'   => Fill::FILL_SOLID,
      'startColor' => ['rgb' => '153346'],
    ],
    'font' => [
      'color' => [
        'rgb' => Color::COLOR_WHITE
      ]
    ]
  ];
  private $itemPrices;

  public function __construct() {}

  public function collection() {
    $itemPriceCollection = new Collection([
      new Collection(['No', 'Kelompok', 'Kode', 'Name', 'Satuan', 'Harga Tiap Provinsi']),
      new Collection(['', '', '', '', ''])
    ]);

    // Setup province columns
    $provinceNames = Province::all()->sortBy('name')->map(function($province) {
      return $province->name;
    });
    foreach ($provinceNames as $provinceName) {
      $itemPriceCollection[1]->push($provinceName);
    }

    // Setup item price columns
    $this->itemPrices = ItemPrice::with(['itemPriceGroup'])->get()->sortBy('item_price_group_id')->values();
    foreach ($this->itemPrices as $index => $itemPrice) {
      $itemPriceRow = new Collection([
        $index + 1,
        $itemPrice->itemPriceGroup->name,
        $itemPrice->id,
        $itemPrice->name,
        $itemPrice->unit->name
      ]);
      $itemPriceProvinces = ItemPriceProvince::where(['item_price_id' => $itemPrice->id])
        ->with('province')
        ->get()
        ->sortBy('province.name')
        ->values();
      foreach ($itemPriceProvinces as $itemPriceProvince) {
        $itemPriceRow->push($itemPriceProvince->price);
      }
      $itemPriceCollection->push($itemPriceRow);
    }
    return $itemPriceCollection;
  }

  public function columnFormats(): array {
    $columnFormats = [];
    foreach ($this->getProvinceColumnKeys() as $columnKey) {
      $columnFormats[$columnKey] = 'Rp* #,##0';
    }
    return $columnFormats;
  }

  public function columnWidths(): array {
    $columnWidths = [
      'A' => 5,
      'B' => 25,
      'C' => 15,
      'D' => 20,
      'E' => 15   
    ];
    foreach ($this->getProvinceColumnKeys() as $provinceColumnKey) {
      $columnWidths[$provinceColumnKey] = 15;
    }
    return $columnWidths;
  }

  private function getProvinceColumnKeys() {
    $provinces = Province::all();
    $columnKeys = new Collection([]);
    for ($i=0; $i < count($provinces); $i++) {
      if((ord('F') + $i) > ord('Z')) {
        $columnKeys[$i] = chr(ord('A')).''.chr(ord('A') + ($i - (ord('Z') - ord('E'))));
        continue;
      }
      $columnKeys[$i] = chr(ord('F') + $i);
    }
    return $columnKeys;
  }

  public function styles(Worksheet $sheet) {
    $styles = [
      'A' => [
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
      ]
    ];
    $headerColumns = new Collection(['A1', 'B1', 'C1', 'D1', 'E1', 'F1']);
    foreach ($this->getProvinceColumnKeys() as $provinceColumnKey) {
      $headerColumns->push($provinceColumnKey.'2');
    }
    foreach ($headerColumns as $headerColumn) {
      $styles[$headerColumn] = $this->headerStyle;
    }
    return $styles;
  }

  public function defaultStyles(Style $defaultStyle) {
    return [
      'alignment' => [
        'wrapText' => true,
        'vertical' => Alignment::VERTICAL_CENTER
      ]
    ];
  }

  public function registerEvents(): array {
    return [
        AfterSheet::class => function(AfterSheet $event) {
          // Merge "No" Column Header
          $event->sheet->mergeCells('A1:A2');

          // Merge "Kelompok" Column Header
          $event->sheet->mergeCells('B1:B2');

          // Merge "Kode" Column Header
          $event->sheet->mergeCells('C1:C2');

          // Merge "Name" Column Header
          $event->sheet->mergeCells('D1:D2'); 

          // Merge "Satuan" Column Header
          $event->sheet->mergeCells('E1:E2'); 

          // Merge "Province" Column Header
          $provinceColumnKeys = $this->getProvinceColumnKeys();
          $event->sheet->mergeCells('F1:'.$provinceColumnKeys[count($provinceColumnKeys) - 1].'1');

          // Freeze all columns before "F" Column
          $workSheet = $event->sheet->getDelegate();
          $workSheet->freezePane('F1');

          // Setup Dropdown List
          $itemPriceGroups = ItemPriceGroup::all();
          $unitOptions = Unit::all()->pluck('name')->toArray();
          for ($i=0; $i < count($this->itemPrices); $i++) {
            // Setup Item Group Dropdown List
            $validation = $event->sheet->getCell("B".($i + 3))->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setError('Value is not in list.');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1(sprintf('"%s"', implode(', ', $itemPriceGroups->map(function($itemPriceGroup){
              return $itemPriceGroup->name;
            })->values()->all())));

            // Setup Unit Dropdown List
            $validation = $event->sheet->getCell("E".($i + 3))->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setError('Value is not in list.');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1(sprintf('"%s"', implode(', ', $unitOptions)));
          }
        },
    ];
  }
}