<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rab;
use App\Models\RabItem;
use App\Models\RabItemHeader;
use App\Models\Unit;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class RabItemController extends Controller
{
    public function index(Project $project, Rab $rab)
    {
        $rabItems = $rab->rabItem;

        return response()->json([
            'status' => 'success',
            'data' => compact('rabItems')
        ]);
    }

    public function store(Project $project, Rab $rab, Request $request)
    {

        $request->merge([
            'rab_id' => $rab->hashidToId($rab->hashid),
            'unit_id' => ($request->has('unit_id') && $request->rab_item_header_id) ? Unit::findByHashid($request->unit_id)->id : Unit::first()->id,
            'rab_item_header_id' => ($request->has('rab_item_header_id') && $request->rab_item_header_id) ? RabItemHeader::findByHashid($request->rab_item_header_id)->id : NULL,
        ]);

        $rabItem = RabItem::create($request->only([
            'rab_id', 'name', 'custom_ahs_id', 'volume', 'unit_id', 'rab_item_header_id'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('rabItem'),
        ]);
    }

    public function update(Project $project, Rab $rab, RabItem $rabItem, Request $request)
    {
        $dataToMerge = [];

        if ($request->has('unit_id') && $request->unit_id) $dataToMerge['unit_id'] = Hashids::decode($request->unit_id)[0];
        if ($request->has('custom_ahs_id') && $request->custom_ahs_id) $dataToMerge['custom_ahs_id'] = Hashids::decode($request->custom_ahs_id)[0];

        $request->merge($dataToMerge);

        $rabItem->update($request->only([
            'name', 'custom_ahs_id', 'volume', 'unit_id', 'price'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('rabItem')
        ]);
    }

    public function destroy(Project $project, Rab $rab, RabItem $rabItem)
    {
        $rabItem->delete();

        return response()->json([
            'status' => 'success'
        ], 204);
    }
}
