<?php

namespace App\Http\Controllers\Master;

use App\Enums\AhsSectionEnum;
use App\Exceptions\CustomException;
use App\Http\Controllers\CountableItemController;
use App\Http\Requests\AhsRequest;

use App\Models\Ahs;
use App\Models\AhsItem;
use App\Models\AhsReferenceGroup;
use App\Models\ItemPrice;
use Vinkla\Hashids\Facades\Hashids;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class AhsController extends CountableItemController
{
  private $masterAhsGroups;
  private $ahsItemTypes;

  public function __construct()
  {
    $this->masterAhsGroups = new Collection([
      ['key' => 'reference', 'title' => 'PUPR 2016'],
      ['key' => 'reference-2023', 'title' => 'PUPR 2023'],
      ['key' => 'reference-2024', 'title' => 'PUPR 2024']
    ]);
    $this->ahsItemTypes = new Collection([
      ['key' => AhsSectionEnum::LABOR->value, 'title' => 'TENAGA KERJA'],
      ['key' => AhsSectionEnum::INGREDIENTS->value, 'title' => 'BAHAN'],
      ['key' => AhsSectionEnum::TOOLS->value, 'title' => 'PERALATAN'],
      ['key' => AhsSectionEnum::OTHERS->value, 'title' => 'LAIN-LAIN']
    ]);
  }

  public function index(Request $request, $ahsId = null)
  {
    $provinceId = Hashids::decode($request->province);
    $query = Ahs::when($ahsId, function ($query) use ($ahsId) {
        return $query->where('id', $ahsId);
      })
      ->with([
          'referenceGroup',
          'ahsItem.unit',
          'ahsItem.ahsItemable' => function ($morph) use ($provinceId) {
              $morph->morphWith([
                ItemPrice::class => ['price' => function ($query) use ($provinceId) {
                  $query->where('province_id', $provinceId);
                }],
            ]);
          }
      ])
      ->orderBy('created_at', 'desc');


    if (isset($request->q)) {
      $query->where('name', 'LIKE', '%' . $request->q . '%')
        ->orWhere('id', 'LIKE', '%' . $request->q . '%');
    }

    if ($request->selected_ahs_group) {
      $query->where('reference_group_id', $request->selected_ahs_group);
    }

    # Paginate AHS
    $isPaginateRequest = $request->has('page') && (int) $request->page > 0;
    $paginationAttribute = [];
    if ($isPaginateRequest) {
      $paginationResult = $this->paginateAhs($query, $request->page, $request->per_page);
      $query = $paginationResult['ahs'];
      $paginationAttribute['total_page'] = $paginationResult['total_page'];
      $paginationAttribute['total_rows'] = $paginationResult['total_rows'];
    };

    $ahs = $query->get();

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

  public function getAhsIds(Request $request)
  {
    // FIXME: Definiteluy need more improovement !
    $ahses = Ahs::all();
    $arrayAhses = [];

    $ahses = $ahses->filter(function ($ahs) {
      foreach ($ahs->ahsItem as $ahsItem) {
        if ($ahsItem->ahs_itemable_type == Ahs::class) return false;
      }
      return true;
    });

    $ahses = $ahses->map(function ($ahs) {
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

  public function store(Request $request)
  {
    $isExists = Ahs::where('code', $request->id)
      ->where('reference_group_id', $request->referenceId)
      ->exists();
    if ($isExists) {
      return response()->json([
        'status' => 'fail',
        'message' => 'Gagal membuat AHS, kode sudah digunakan'
      ], 422);
      return;
    }
    $ahs = Ahs::create([
      'code' => $request->id,
      'name' => $request->name,
      'reference_group_id' => $request->referenceId
    ]);
    return response()->json([
      'status' => 'success',
      'data' => $ahs
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
        'id',
        'name'
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

  public function import(Request $request)
  {
    $this->validate($request, [
      'file' => 'required|mimes:csv,xls,xlsx'
    ]);
    $uploadedFile = $request->file('file');
    $fileName = $uploadedFile->hashName();
    $temporaryPath = $uploadedFile->storeAs('public/excel/', $fileName);
    try {
      Ahs::query()->delete();
      AhsItem::truncate();
      Excel::import(
        new MasterAhsImportController(
          $this->masterAhsGroups,
          $this->ahsItemTypes
        ),
        storage_path('app/public/excel/' . $fileName)
      );
      Storage::delete($temporaryPath);
      return response()->json([
        'status' => 'success',
        'data' => Ahs::all()
      ]);
    } catch (CustomException $e) {
      return response()->json([
        'status' => 'fail',
        'message' => $e->getMessage(),
      ], 400);
    } catch (Throwable $error) {
      $error_message = 'Gagal mengubah/ menambah data, cek kembali excel yang diupload';
      if ($error instanceof CustomException) {
        $error_message = $error->getMessage();
      }
      return response()->json([
        'status' => 'fail',
        'message' => $error_message,
        'dev_message' => $error->getMessage()
      ], 400);
    }
  }

  public function export()
  {
    $ahsReferenceGroups = AhsReferenceGroup::all(); 

    return Excel::download(
      new MasterAhsExportController(
        $ahsReferenceGroups,
        $this->ahsItemTypes
      ),
      'Master Ahs.xlsx'
    );
  }

  public function fetchMasterAhsProject(Request $request)
  {
    $ahsQuery = Ahs::query();
    if ($request->filled('referenceGroupId')) {
      $ahsQuery->where('reference_group_id', $request->referenceGroupId);
    }
    $masterAhsItems = $ahsQuery
      ->where(function ($query) use ($request) {
        $query->where('id', 'LIKE', "%$request->q%")
          ->orWhere('name', 'LIKE', "%$request->q%");
      })
      ->take($request->limit)
      ->select(['id', 'code', 'name'])
      ->without('ahsItem')
      ->latest()
      ->get();
    $mutatedMasterAhsItems = $masterAhsItems->map(function ($data) {
      return $data;
    })->toArray();
    return response()->json([
      'status' => 'success',
      'data' => [
        'ahsList' => $mutatedMasterAhsItems
      ]
    ]);
  }
}
