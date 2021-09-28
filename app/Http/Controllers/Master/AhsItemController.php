<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Ahs;
use App\Models\AhsItem;
use Illuminate\Http\Request;

class AhsItemController extends Controller
{

    public function index(Request $request)
    {

        if (is_null($request->ahs)) $ahs = AhsItem::with('ahsItemable')->get();
        else $ahs = AhsItem::with('ahsItemable')->get();

        return dd($ahs);

        return response()->json([
            'status' => 'success',
            'data' => $request->ahs
        ]);
    }

    public function store(Request $request, Ahs $ahs)
    {
        AhsItem::create($request->only([
            'ahs_id', 'name', 'unit_id', 'coefficient', 'section', 'ahs_itemable_id', 'ahs_itemable_type',
        ]));

        return response()->json([
            'status' => 'success',
            'data' => AhsItem::all()
        ]);
    }
}
