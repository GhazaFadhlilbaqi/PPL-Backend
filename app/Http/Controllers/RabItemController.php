<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rab;
use App\Models\RabItem;
use App\Models\RabItemHeader;
use App\Models\Unit;
use Illuminate\Http\Request;

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
            'unit_id' => Unit::findByHashid($request->unit_id)->id,
            'rab_item_header_id' => RabItemHeader::findByHashid($request->rab_item_header_id)->id,
        ]);

        $rabItem = RabItem::create($request->only([
            'rab_id', 'name', 'ahs_id', 'volume', 'unit_id', 'rab_item_header_id'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('rabItem'),
        ]);
    }

    public function update(Project $project, Rab $rab, RabItem $rabItem, Request $request)
    {
        $rabItem->update($request->only([
            'name', 'ahs_id', 'volume', 'unit_id'
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
