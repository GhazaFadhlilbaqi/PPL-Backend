<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemPriceGroupRequest;
use App\Models\ItemPriceGroup;
use Illuminate\Http\Request;
use Exception;

class ItemPriceGroupController extends Controller
{
    public function index(Request $request)
    {

        if ($request->has('datatable') && $request->datatable == 'false') {

            $itemPriceGroups = ItemPriceGroup::all();

            return response()->json([
                'status' => 'success',
                'data' => compact('itemPriceGroups'),
            ]);
        }

        return $this->getTableFormattedData(ItemPriceGroup::query())->make();
    }

    public function store(ItemPriceGroupRequest $request)
    {
        if (str_contains($request->name, ',')) {
          return response()->json([
            'status' => 'fail',
            'message' => "Nama tidak boleh memuat tanda koma",
          ], 400);
        }
        $itemPrice = ItemPriceGroup::create($request->only(['name']));
        return response()->json([
            'status' => 'success',
            'data' => compact('itemPrice'),
        ]);
    }

    public function destroy(ItemPriceGroup $itemPriceGroup)
    {
        try {
            $itemPriceGroup->delete();
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

    public function update(ItemPriceGroup $itemPriceGroup, ItemPriceGroupRequest $request)
    {
        if (str_contains($request->name, ',')) {
          return response()->json([
            'status' => 'fail',
            'message' => "Nama tidak boleh memuat tanda koma",
          ], 400);
        }
        $itemPriceGroup->update($request->only(['name']));
        return response()->json([
            'status' => 'success',
            'data' => compact('itemPriceGroup')
        ]);
    }
}
