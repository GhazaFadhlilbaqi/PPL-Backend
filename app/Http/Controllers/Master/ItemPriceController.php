<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ItemPriceBatchUpdateRequest;
use App\Http\Requests\ItemPriceRequest;
use App\Models\AhsItem;
use App\Models\ItemPrice;
use App\Models\ItemPriceProvince;
use App\Models\Province;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;

class ItemPriceController extends Controller
{
  public function index(Request $request)
  {
    // 1) Validate request
    $request->validate([
      'group_id' => 'required',
      'province_id' => 'required'
    ]);

    // 2) Query item price based on province id & group id
    $decodedProvinceId = Hashids::decode($request->province_id);
    $decodedGroupId = Hashids::decode($request->group_id);
    $query = ItemPrice::where('item_price_group_id', $decodedGroupId)
      ->with([
        'unit:id,name',
        'price' => function ($q) use ($decodedProvinceId) {
          $q->select('item_price_id', 'price', 'province_id')
            ->where('province_id', $decodedProvinceId);
        }
      ])
      ->orderBy('created_at', 'desc');
    $queryResults = $query->paginate($request->query('limit', 15));

    // 3) Transform response into frontend needs
    $queryResults->getCollection()->transform(function ($itemPrice) {
      $province_price = $itemPrice->price->first();
      return [
        'id' => $itemPrice->id,
        'name' => $itemPrice->name,
        'unit' => $itemPrice->unit,
        'price' => $province_price ? $province_price->price : 0,
      ];
    });
    return [
      'status' => 'success',
      'data' => [
        'item_prices' => $queryResults->items(),
        'pagination_attribute' => [
          'total_page' => $queryResults->lastPage(),
          'total_data' => $queryResults->total()
        ]
      ]
    ];
  }

  public function store(ItemPriceRequest $request)
  {
    // 1) Create Item Price
    $request->validate([
      'id' => 'required',
      'item_price_group_id' => 'required',
      'name' => 'required',
      'price' => 'required|numeric',
      'unit_id' => 'required'
    ]);
    $request['item_price_group_id'] = Hashids::decode($request->item_price_group_id)[0];
    $request['unit_id'] = Hashids::decode($request->unit_id)[0];
    $itemPrice = ItemPrice::create(
      $request->only(['id', 'item_price_group_id', 'unit_id', 'name'])
    );

    // 2) Create Item Price Provinces
    ItemPriceProvince::where('item_price_id', $itemPrice->id)->delete();
    $itemPriceProvinces = Province::all()->map(function ($province) use ($itemPrice, $request) {
      return [
        'province_id' => $province->id,
        'item_price_id' => $itemPrice->id,
        'price' => $request->price,
        'created_at' => Carbon::now(),
      ];
    });
    ItemPriceProvince::insert($itemPriceProvinces->toArray());

    return response()->json([
      'status' => 'success',
      'data' => compact('itemPrice'),
    ]);
  }

  public function setPrice(Request $request, ItemPrice $itemPrice)
  {

    $request->merge([
      'province_id' => Hashids::decode($request->province_id)[0],
      'price' => $request->price ?? 0,
    ]);

    $existItemPrice = ItemPriceProvince::where('item_price_id', $itemPrice->id)->where('province_id', $request->province_id);

    if (!$existItemPrice->count()) {
      $itemPrice = ItemPriceProvince::create(
        array_merge(['item_price_id' => $itemPrice->id], $request->only(['province_id', 'price'])),
      );
    } else {
      $existItemPrice->update([
        'price' => $request->price,
      ]);
    }


    return response()->json([
      'status' => 'success',
      'data' => compact('itemPrice'),
    ]);
  }

  public function destroy(ItemPrice $itemPrice)
  {
    // 1) Check dependant ahs items
    $ahsItems = AhsItem::where('ahs_itemable_id', $itemPrice->id);
    if ($ahsItems->count()) {
      return response()->json([
        'status' => 'fail',
        'message' => 'Masih ada item ahs lain yang bergantung dengan harga satuan ini !',
      ], 400);
    }

    // 2) Remove item price
    $itemPrice->delete();

    return response()->json([
      'status' => 'success',
    ]);
  }

  # NOTE: Should using ItemPriceRequest validation request class
  public function update($item_price_id, Request $request)
  {
    // 1) Validate request
    $request->validate([
      'group_id' => 'required',
      'id' => 'required',
      'name' => 'required',
      'price' => 'required|numeric',
      'unit_id' => 'required'
    ]);
    $request['group_id'] = Hashids::decode($request->group_id)[0];
    $request['unit_id'] = Hashids::decode($request->unit_id)[0];

    // 2) Update item price
    ItemPrice::where('id', $item_price_id)->update(
      $request->only(['id', 'item_price_group_id', 'unit_id', 'name'])
    );

    // 3) Update item price province
    ItemPriceProvince::where([
      'item_price_id' => $request->id
    ])->update($request->only(['price']));

    // 4) Update related AHS
    if ($item_price_id != $request->id) {
      AhsItem::where('ahs_itemable_id', $item_price_id)->update([
        'ahs_itemable_id' => $request->id
      ]);
    }

    return response()->json([
      'status' => 'success',
      'data' => null,
    ]);
  }

  public function batchUpdatePrice(ItemPrice $itemPrice, ItemPriceBatchUpdateRequest $request)
  {
    $provincesWithItemPrice = Province::all()->map(function ($province) use ($itemPrice, $request) {
      return [
        'province_id' => $province->id,
        'item_price_id' => $itemPrice->id,
        'price' => $request->price,
        'created_at' => Carbon::now(),
      ];
    });

    ItemPriceProvince::where('item_price_id', $itemPrice->id)->delete();
    ItemPriceProvince::insert($provincesWithItemPrice->toArray());

    return response()->json([
      'status' => 'success',
    ]);
  }

  public function export()
  {
    return Excel::download(
      new MasterItemPriceExportController(),
      'Master Unit Price.xlsx'
    );
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
      Excel::import(
        new MasterItemPriceImportController(),
        storage_path('app/public/excel/' . $fileName)
      );
      Storage::delete($temporaryPath);
      return response()->json([
        'status' => 'success',
        'data' => ItemPrice::all()
      ]);
    } catch (Exception $error) {
      \Sentry\captureException($error);
      $errorMessage = 'Gagal mengubah/ menambah data, cek kembali excel yang diupload';
      if ($error instanceof CustomException) {
        $errorMessage = $error->getMessage();
      }
      return response()->json([
        'status' => 'fail',
        'message' => $errorMessage
      ], 400);
    }
  }
}
