<?php

namespace App\Http\Controllers\Master;

use App\Enums\AhsSectionEnum;
use App\Http\Controllers\CountableItemController;
use App\Models\Ahs;
use App\Models\AhsItem;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterAhsExportController implements WithMultipleSheets {
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
  private $masterAhsGroups;
  private $ahsItemTypes;

  public function __construct($masterAhsGroups, $ahsItemTypes) {
    $this->masterAhsGroups = $masterAhsGroups;
    $this->ahsItemTypes = $ahsItemTypes;
  }

  public function sheets(): array {
      return [
        new MasterAhsExportSheet($this->headerStyle, $this->masterAhsGroups),
        new MasterAhsItemExportSheet($this->headerStyle, $this->ahsItemTypes)
      ];
  }
}

class MasterAhsExportSheet extends CountableItemController implements FromCollection, WithEvents, WithColumnWidths, WithStyles, WithTitle {
  private $headerStyle;
  private $masterAhsGroups;

  public function __construct($headerStyle, $masterAhsGroups) {
    $this->headerStyle = $headerStyle;
    $this->masterAhsGroups = $masterAhsGroups;
  }

  public function collection() {
      $ahsCollection = new Collection([
        ['No', 'Kode', 'Groups', 'Name']
      ]);
      $ahsList = Ahs::all();
      $this->totalAhsCount = count($ahsList);
      foreach ($ahsList as $index => $ahs) {
        $ahsCollection->push([
          $index + 1,
          $ahs->id,
          $this->masterAhsGroups->first(function($masterAhsGroup) use ($ahs) {
            return $masterAhsGroup['key'] == $ahs->groups;
          })['title'],
          $ahs->name
        ]);
      }
      return $ahsCollection;
  }

  public function title(): string {
      return 'AHS';
  }

  public function columnWidths(): array {
    return [
        'A' => 5,
        'B' => 15,
        'C' => 15,
        'D' => 40,            
    ];
  }

  public function styles(Worksheet $sheet) {
    return [
      'A' => [
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
      ],
      'A1' => $this->headerStyle,
      'B1' => $this->headerStyle,
      'C1' => $this->headerStyle,
      'D1' => $this->headerStyle
    ];
  }

  public function registerEvents(): array {
    return [
        AfterSheet::class => function(AfterSheet $event) {
          for ($i=0; $i < $this->totalAhsCount; $i++) {
            // Setup Ahs Groups Dropdown List
            $validation = $event->sheet->getCell("C".($i + 2))->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setError('Value is not in list.');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1(sprintf('"%s"', implode(', ', $this->masterAhsGroups->map(function($masterAhsGroups){
              return $masterAhsGroups['title'];
            })->values()->all())));
          }
        },
    ];
  }
}

class MasterAhsItemExportSheet extends CountableItemController implements FromCollection, WithColumnWidths, WithEvents, WithStrictNullComparison, WithStyles, WithTitle {
  private $ahsItemTypes;
  private $totalAhsItemCount = 0;
  private $headerStyle;

  public function __construct($headerStyle, $ahsItemTypes) {
    $this->headerStyle = $headerStyle;
    $this->ahsItemTypes = $ahsItemTypes;
  }

  public function collection() {
      $ahsItemCollection = new Collection([
        ['No', 'Kode AHS', 'Tipe', 'Kode Item', 'Nama', 'Satuan', 'Koefisien']
      ]);
      $ahsItems = AhsItem::with('unit')->with('ahsItemable')
        ->get()
        ->sortBy(function($item) {
          return array_search(
            $item->section,
            [
                AhsSectionEnum::LABOR->value,
                AhsSectionEnum::INGREDIENTS->value,
                AhsSectionEnum::TOOLS->value,
                AhsSectionEnum::OTHERS->value
            ]
          );
        })
        ->sortBy('ahs_id')
        ->values();
      $this->totalAhsItemCount = count($ahsItems);
      foreach ($ahsItems as $index => $ahsItem) {
        $ahsItemCollection->push([
          $index + 1,
          $ahsItem->ahs_id,
          $this->ahsItemTypes->first(function ($value, $key) use ($ahsItem) {
            return $value['key'] == $ahsItem->section;
          })['title'],
          $ahsItem->ahs_itemable_id,
          $ahsItem->ahsItemable !== null
            ? $ahsItem->ahsItemable->name
            : $ahsItem->name,
          $ahsItem->ahsItemable !== null
            ? $ahsItem->ahsItemable->unit->name ?? ""
            : $ahsItem->unit->name ?? "",
          $ahsItem->coefficient,
        ]);
      }
      return $ahsItemCollection;
  }

  public function title(): string {
      return 'AHS ITEM';
  }

  public function registerEvents(): array {
    return [
        AfterSheet::class => function(AfterSheet $event) {
          $unitOptions = Unit::all()->pluck('name')->toArray();
          for ($i=0; $i < $this->totalAhsItemCount; $i++) {
            // Setup Ahs Code Dropdown List
            $validation = $event->sheet->getCell("B".($i + 2))->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setError('Value is not in list.');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1('AHS!$B$2:$B$9999');

            // Setup Section Dropdown List
            $validation = $event->sheet->getCell("C".($i + 2))->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setError('Value is not in list.');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1(sprintf('"%s"', implode(', ', $this->ahsItemTypes->map(function($ahsItemType){
              return $ahsItemType['title'];
            })->values()->all())));

            // Setup Unit Dropdown List
            $validation = $event->sheet->getCell("F".($i + 2))->getDataValidation();
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

  public function columnWidths(): array {
    return [
        'A' => 5,
        'B' => 18,
        'C' => 18,
        'D' => 18,
        'E' => 18,
        'F' => 18,
        'G' => 10         
    ];
  }

  public function styles(Worksheet $sheet) {
    return [
      'A' => [
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
      ],
      'A1' => $this->headerStyle,
      'B1' => $this->headerStyle,
      'C1' => $this->headerStyle,
      'D1' => $this->headerStyle,
      'E1' => $this->headerStyle,
      'F1' => $this->headerStyle,
      'G1' => $this->headerStyle
    ];
  }
}