<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\CountableItemController;
use App\Http\Requests\AhsRequest;

use App\Models\Ahs;
use App\Models\AhsItem;

use Vinkla\Hashids\Facades\Hashids;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AhsController extends CountableItemController
{
  private $masterAhsGroups;
  private $ahsItemTypes;

  public function __construct() {
    $this->masterAhsGroups = new Collection([
      ['key' => 'reference', 'title' => 'PUPR 2016'],
      ['key' => 'reference-2023', 'title' => 'PUPR 2023']
    ]);
    $this->ahsItemTypes = new Collection([
      ['key' => 'labor', 'title' => 'TENAGA KERJA'],
      ['key' => 'ingredients', 'title' => 'BAHAN'],
      ['key' => 'tools', 'title' => 'PERALATAN'],
      ['key' => 'others', 'title' => 'LAIN-LAIN']
    ]);
  }

    public function index(Request $request, $ahsId = null)
    {
        $ahs = !is_null($ahsId) ? Ahs::where('id', $ahsId) : Ahs::query();
        $ahs = $ahs->with(['ahsItem' => function($ahsItem) { $ahsItem->with(['ahsItemable', 'unit']); }])->orderBy('created_at', 'ASC');
        $isPaginateRequest = $request->has('page') && (int) $request->page > 0;
        $paginationAttribute = [];

        if ($request->selected_ahs_group && $request->selected_ahs_group != '' && $request->selected_ahs_group != 'all') {
            $ahs->where('groups', $request->selected_ahs_group);
        }

        # Paginate AHS
        if ($isPaginateRequest) {
            $paginationResult = $this->paginateAhs($ahs, $request->page, $request->per_page);
            $ahs = $paginationResult['ahs'];
            $paginationAttribute['total_page'] = $paginationResult['total_page'];
            $paginationAttribute['total_rows'] = $paginationResult['total_rows'];
        };

        $ahs = $ahs->get();

        $provinceId = Hashids::decode($request->province);

        # Categorizing by section
        if ($request->arrange == 'true' && $request->has('province')) {

            $itemArranged = [];

            foreach ($ahs as $key => $a) {

                # Categorizing by it's section. (e.g labor, ingredients, etc)
                foreach ($a->ahsItem as $key2 => $aItem) $itemArranged[$aItem->section][] = $aItem;

                $ahs[$key]['item_arranged'] = $itemArranged;

                $itemArranged = [];
                $ahs[$key] = $this->countAhsSubtotal($a, $provinceId);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'ahs' => $ahs,
                'pagination_attribute' => $paginationAttribute,
            ]
        ]);
    }

    public function getAhsIds()
    {

        // FIXME: Definiteluy need more improovement !
        $ahses = Ahs::all();
        $arrayAhses = [];

        $ahses = $ahses->filter(function($ahs) {
            foreach ($ahs->ahsItem as $ahsItem) {
                if ($ahsItem->ahs_itemable_type == Ahs::class) return false;
            }
            return true;
        });

        $ahses = $ahses->map(function($ahs) {
            return [
                'name' => $ahs->name,
                'code' => $ahs->code,
                'groups' => $ahs->groups,
                'id' => $ahs->id,
            ];
        });

        foreach ($ahses as $ahs) {
            $arrayAhses[] = $ahs;
        }

        $ahses = $arrayAhses;

        return response()->json([
            'status' => 'success',
            'data' => compact('ahses')
        ]);
    }

    public function store(AhsRequest $request)
    {
        $createdAhs = Ahs::create($request->only(['id', 'name', 'groups']));
        return response()->json([
            'status' => 'success',
            'data' => $createdAhs
        ]);
    }

    public function destroy(Ahs $ahs)
    {

        # Check if there are any ahs depends on this ahs, prevent to delete !
        $dependantsAhsItem = AhsItem::where('ahs_itemable_id', $ahs->id);

        if ($dependantsAhsItem->count()) {
            # FIXME: Make better error handler
            return response()->json([
                'status' => 'fail',
                'message' => 'Masih ada item ahs lain yang bergantung dengan AHS ini !',
            ], 400);
        }

        $ahs->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'AHS Deleted'
        ], 204);
    }

    public function update(AhsRequest $request, Ahs $ahs)
    {
        try {
            DB::beginTransaction();
            if ($request->has('id') && ($ahs->id != $request->id)) {

                $oldId = $ahs->id;

                AhsItem::where('ahs_itemable_id', $oldId)->update([
                    'ahs_itemable_id' => $request->id,
                ]);
            }

            $ahs->update($request->only([
                'id', 'name'
            ]));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => compact('ahs')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah data'
            ]);
        }
    }

    private function paginateAhs($ahs, $currentPage, $ahsPerPage)
    {
        $totalRows = $ahs->count();
        $totalPage = ceil($totalRows / (int) $ahsPerPage);
        $currentIndexStart = ((int) $ahsPerPage * (int) $currentPage) - (int) $ahsPerPage;

        $ahs = $ahs->skip($currentIndexStart)->take((int) $ahsPerPage);

        return [
            'total_page' => $totalPage,
            'current_page' => $currentPage,
            'current_index_range' => [$currentIndexStart, $currentIndexStart + (int) $ahsPerPage],
            'total_rows' => $totalRows,
            'ahs' => $ahs
        ];
    }

    public function import(Request $request) {
      $this->validate($request, [
        'file' => 'required|mimes:csv,xls,xlsx'
      ]);
      $uploadedFile = $request->file('file');
      $fileName = $uploadedFile->hashName();
      $temporaryPath = $uploadedFile->storeAs('public/excel/', $fileName);
      try {
        Excel::import(
          new MasterAhsImportController(
            $this->masterAhsGroups,
            $this->ahsItemTypes
          ),
          storage_path('app/public/excel/'.$fileName)
        );
        Storage::delete($temporaryPath);
        return response()->json([
          'status' => 'success',
          'data' => Ahs::all()
        ]);
      } catch(Exception) {
        return response()->json([
          'status' => 'error',
          'message' => 'Gagal menambah data'
        ]);
      }
    }

    public function export() {
      return Excel::download(
        new MasterAhsExportController(
          $this->masterAhsGroups,
          $this->ahsItemTypes
        ),
        'Master Ahs.xlsx'
      );
    }
}

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
        ['No', 'Kode AHS', 'Tipe', 'Kode Item', 'Koefisien']
      ]);
      $ahsItems = AhsItem::all()
        ->sortBy(function($item) {
          return array_search(
            $item->section,
            ['labor', 'ingredients', 'tools', 'others']
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
          for ($i=0; $i < $this->totalAhsItemCount; $i++) {
            // Setup Ahs Code Dropdown List
            $validation = $event->sheet->getCell("B".($i + 2))->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setError('Value is not in list.');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1('AHS!$B$2:$B$998');

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
        'E' => 10         
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
      'E1' => $this->headerStyle
    ];
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
        AhsItem::create([
          'ahs_id' => $row[1],
          'section' => $this->ahsItemTypes->first(function($ahsItemType) use ($row) {
            return $ahsItemType['title'] == $row[2];
          })['key'],
          'ahs_itemable_id' => $row[3],
          'ahs_itemable_type' => $isAhsReference
            ? 'App\\Models\\Ahs'
            : 'App\\Models\\ItemPrice',
          'coefficient' => $row[4],
        ]);
      }
  }
}