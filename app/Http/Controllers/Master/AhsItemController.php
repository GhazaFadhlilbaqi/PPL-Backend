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
        if ($request->has('ahs'))
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
