<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\AhsItemRequest;
use App\Models\Ahp;
use App\Models\Ahs;
use App\Models\AhsItem;
use App\Models\ItemPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Vinkla\Hashids\Facades\Hashids;

class AhsItemController extends Controller
{

    public function index(Request $request, $ahs)
    {
        $ahs = AhsItem::where('ahs_id', $ahs)->with('ahsItemable')->get();

        return response()->json([
            'status' => 'success',
            'data' => compact('ahs'),
        ]);
    }

    public function store(AhsItemRequest $request, Ahs $ahs)
    {

        $request->merge([
            'ahs_id' => $ahs->id,
            'ahs_itemable_type' => "App\\Models\\" . $request->ahs_itemable_type,
        ]);

        AhsItem::create($request->only([
            'ahs_id', 'name', 'unit_id', 'coefficient', 'section', 'ahs_itemable_id', 'ahs_itemable_type',
        ]));

        return response()->json([
            'status' => 'success',
            'data' => AhsItem::all()
        ]);
    }

    public function getAhsItemableId()
    {

        $ahsItemableIds = $this->generateItemableIds();

        return response()->json([
            'status' => 'success',
            'data' => compact('ahsItemableIds'),
        ]);
    }


    public function update(AhsItem $ahsItem, Request $request)
    {

        $dataToMerge = [];
        $currentAhsItemItemableType = explode('\\', $ahsItem->ahs_itemable_type)[2];
        $newAhsItemItemableType = $request->ahs_itemable_type;

        if ($request->has('unit_id')) $dataToMerge['unit_id'] = Hashids::decode($request->unit_id)[0];
        if ($request->has('ahs_itemable_type')) $dataToMerge['ahs_itemable_type'] = 'App\\Models\\' . $request->ahs_itemable_type;

        # Because when ahs item is referenced to ahs, it has customable name
        # TODO: Need more tiddier way to compare between old and new itemable type
        if (($currentAhsItemItemableType === 'ItemPrice') && ($newAhsItemItemableType === 'Ahp' || $newAhsItemItemableType === 'Ahs')) {
            if ($newAhsItemItemableType === 'Ahs') $dataToMerge = array_merge($dataToMerge, ['name' => Ahs::where('id', $request->ahs_itemable_id)->first()->name]);
            else $dataToMerge = array_merge($dataToMerge, ['name' => Ahp::where('id', $request->ahs_itemable_id)->first()->name]);
        } else if (($currentAhsItemItemableType === 'Ahs' || $currentAhsItemItemableType === 'Ahp') && $newAhsItemItemableType === 'ItemPrice') {
            $dataToMerge = array_merge($dataToMerge, ['name' => null, 'unit_id' => null]);
        } else if (($currentAhsItemItemableType === 'Ahp' && $newAhsItemItemableType === 'Ahs') || ($currentAhsItemItemableType === 'Ahs' && $newAhsItemItemableType === 'Ahp')) {
            $dataToMerge = array_merge($dataToMerge, ['name' => ('App\\Models\\' . $newAhsItemItemableType)::where('id', $request->ahs_itemable_id)->first()->name]);
        }

        $request->merge($dataToMerge);

        $ahsItem->update($request->only([
            'name', 'ahs_itemable_id', 'ahs_itemable_type', 'coefficient', 'unit_id'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('ahsItem')
        ]);
    }

    public function destroy(AhsItem $ahsItem)
    {
        $ahsItem->delete();
        return response()->json([
            'status' => 'success',
        ], 204);
    }

    private function generateItemableIds()
    {
        # Get item price ids
        $itemPriceIds = ItemPrice::all()->map(function($itemPrice) {
            return [
                'ahs_itemable_type' => "App\\Models\\ItemPrice",
                'id' => $itemPrice->id,
                'display_id' => $itemPrice->id
            ];
        });

        # Get ahs ids
        $ahsIds = Ahs::all()->map(function($ahs) {
            return [
                'ahs_itemable_type' => "App\\Models\\Ahs",
                'id' => $ahs->id,
                'display_id' => $ahs->id,
            ];
        });

        # Get AHP ids
        $ahpIds = Ahp::all()->map(function($ahp) {
            return [
                'ahs_itemable_type' => 'App\\Models\\Ahp',
                'id' => $ahp->id,
                'display_id' => $ahp->id,
            ];
        });

        return ($itemPriceIds->count()) ? $itemPriceIds->merge($ahsIds)->merge($ahpIds) : [];
    }
}
