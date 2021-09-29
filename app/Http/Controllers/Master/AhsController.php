<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\AhsRequest;
use App\Models\Ahs;
use App\Models\ItemPrice;
use App\Models\Province;
use Exception;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Request;

class AhsController extends Controller
{

    public function index(Request $request, $ahsId = null)
    {

        $ahs = !is_null($ahsId) ? Ahs::where('id', $ahsId) : Ahs::query();
        $ahs = $ahs->with(['ahsItem' => function($ahsItem) { $ahsItem->with(['ahsItemable', 'unit']); }])->get();
        $provinceId = Hashids::decode($request->province);

        # Categorizing by section
        if ($request->arrange == 'true' && $request->has('province')) {

            $itemArranged = [];
            foreach ($ahs as $key => $a) {
                foreach ($a->ahsItem as $key2 => $aItem) $itemArranged[$aItem->section][] = $aItem;
                $ahs[$key]['item_arranged'] = $itemArranged;
                $itemArranged = [];
                $ahs[$key] = $this->countAhsSubtotal($a, $provinceId);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => compact('ahs')
        ]);
    }

    public function store(AhsRequest $request)
    {
        Ahs::create($request->only(['id', 'name']));

        return response()->json([
            'status' => 'success',
            'data' => Ahs::all()
        ]);
    }

    private function countAhsItemTotal($ahsItem, $province = null)
    {
        # Check if ahsItem referenced to item price
        if ($ahsItem->ahs_itemable_type === ItemPrice::class) {

            $itemPrice = $ahsItem->ahsItemable->with(['price' => function($q) use ($province) {
                $q->where('province_id', $province);
            }])->first();

            $fixedPrice = count($itemPrice->price) > 0 ? $itemPrice->price[0]->price : 0;
            $ahsItem->ahsItemable->subtotal = $fixedPrice;

            return $fixedPrice * $ahsItem->coefficient;

        } else if ($ahsItem->ahs_itemable_type === Ahs::class) {
            return $this->countAhsSubtotal($ahsItem->ahsItemable, $province)->subtotal * $ahsItem->coefficient;
        } else {
            throw new Exception('Itemable type not compatible with counting');
        }
    }

    private function countAhsSubtotal($ahs, $province = null)
    {
        $ahsSubtotal = 0;

        foreach ($ahs->ahsItem as $ahsItem) {
            $ahsItem->subtotal = $this->countAhsItemTotal($ahsItem, $province);
            $ahsSubtotal += $ahsItem->subtotal;
        }

        $ahs->subtotal = $ahsSubtotal;

        return $ahs;

    }
}
