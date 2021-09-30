<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemPriceBatchUpdateRequest;
use App\Http\Requests\ItemPriceRequest;
use App\Models\ItemPrice;
use App\Models\ItemPriceGroup;
use App\Models\ItemPriceProvince;
use App\Models\Province;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class ItemPriceController extends Controller
{
    public function index(Request $request)
    {

        $itemPrices = null;

        if ($request->has('grouped') && $request->grouped == 'true') {

            $itemPriceGroups = ItemPriceGroup::query();
            $id = Hashids::decode($request->province);

            if ($request->has('province')) {
                $itemPriceGroups->with(['itemPrice' => function($q) use ($request, $id) {
                    $q->with(['price' => function($q) use ($id) {
                        $q->where('province_id', $id ?? -1);
                    }, 'unit', 'itemPriceGroup'])->orderBy('created_at');
                }]);
            }

            $itemPrices = $itemPriceGroups->orderBy('created_at', 'ASC')->get();

        } else {
            $itemPrices = ItemPrice::all();
        }

        return [
            'status' => 'success',
            'data' => compact('itemPrices')
        ];
    }

    public function store(ItemPriceRequest $request)
    {

        $request->merge([
            'item_price_group_id' => Hashids::decode($request->item_price_group_id)[0],
            'unit_id' => Hashids::decode($request->unit_id)[0],
        ]);

        $itemPrice = ItemPrice::create($request->only(['id', 'item_price_group_id', 'unit_id', 'name']));

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
        try {
            $itemPrice->delete();
            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    # NOTE: Should using ItemPriceRequest validation request class
    public function update($itemPriceId, Request $request)
    {

        $request->merge([
            'item_price_group_id' => Hashids::decode($request->item_price_group_id)[0],
            'unit_id' => Hashids::decode($request->unit_id)[0]
        ]);

        $itemPrice = ItemPrice::where('id', $itemPriceId)->update($request->only(['id', 'item_price_group_id', 'unit_id', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => compact('itemPrice'),
        ]);
    }

    public function batchUpdatePrice(ItemPrice $itemPrice, ItemPriceBatchUpdateRequest $request)
    {
        $provincesWithItemPrice = Province::all()->map(function($province) use ($itemPrice, $request) {
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
}
