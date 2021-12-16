<?php

namespace App\Http\Controllers;

use App\Models\CustomAhsItem;
use App\Models\CustomItemPrice;
use App\Models\Project;
use App\Traits\UnitTrait;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class CustomAhsItemController extends Controller
{

    use UnitTrait;

    public function index()
    {

    }

    public function store(Project $project, Request $request)
    {
        $customAhsItem = CustomAhsItem::create([
            'unit_id' => $this->getFirstUnit()->id,
            'coefficient' => 0.0,
            'section' => $request->section,
            'custom_ahs_itemable_type' => CustomItemPrice::class,
            'custom_ahs_itemable_id' => $project->customItemPrice->first()->id,
            'custom_ahs_id' => Hashids::decode($request->custom_ahs_id)[0]
        ]);

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhsItem')
        ]);
    }

    public function destroy(Project $project, CustomAhsItem $customAhsItem)
    {

        $customAhsItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted'
        ], 204);
    }

    public function update(Project $project, CustomAhsItem $customAhsItem, Request $request)
    {
        $customAhsItem->update($request->only([
            'name', 'unit_id', 'coefficient', 'section', 'custom_ahs_itemable_id', 'custom_ahs_itemable_type'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => 'Berhasil mengupdate item AHS'
        ]);
    }
}
