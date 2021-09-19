<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemPriceRequest;
use App\Models\ItemPrice;
use App\Models\ItemPriceProvince;
use Illuminate\Http\Request;

class ItemPriceController extends Controller
{
    public function index()
    {

        $itemPrices = ItemPrice::with(['itemPriceGroup', 'unit', 'price.province'])->get();

        return [
            'status' => 'success',
            'data' => compact('itemPrices')
        ];
    }

    public function store(ItemPriceRequest $request)
    {
        $itemPrice = ItemPrice::create($request->only(['id', 'item_price_group_id', 'unit_id', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => compact('itemPrice'),
        ]);
    }

    public function setPrice(Request $request, ItemPrice $itemPrice)
    {
        $itemPrice = ItemPriceProvince::create(
            array_merge(['item_price_id' => $itemPrice->id], $request->only(['province_id', 'price'])),
        );
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
        $itemPrice = ItemPrice::where('id', $itemPriceId)->update($request->only(['id', 'item_price_group_id', 'unit_id', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => compact('itemPrice'),
        ]);
    }
}
