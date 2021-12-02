<?php

namespace App\Http\Controllers;

use App\Models\CustomItemPrice;
use App\Models\CustomItemPriceGroup;
use App\Models\ItemPriceGroup;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomItemPriceGroupController extends Controller
{
    public function index(Project $project)
    {

        $itemPriceGroups = ItemPriceGroup::with(['itemPrice', 'customItemPrice' => function($q) use ($project) {
            $q->where('project_id', $project->hashidToId($project->hashid));
        }])->get();

        $customItemPriceGroups = CustomItemPriceGroup::where('project_id', $project->hashidToId($project->hashid))->with('customItemPrice')->get();

        $mergedCustomItemPrices = array_merge($itemPriceGroups->toArray(), $customItemPriceGroups->toArray());

        return response()->json([
            'status' => 'success',
            'data' => [
                'customItemPriceGroups' => $mergedCustomItemPrices,
            ]
        ]);
    }

    public function store(Project $project, Request $request)
    {

        $request->merge(['project_id' => $project->hashidToId($project->hashid)]);

        $customItemPrice = CustomItemPriceGroup::create($request->only([
            'name', 'project_id'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customItemPrice')
        ]);
    }

    public function destroy(Project $project, CustomItemPriceGroup $customItemPriceGroup)
    {

        // Delete all childs
        // TODO: Find all ahs that related to this item price
        CustomItemPrice::where('custom_item_priceable_type', CustomItemPriceGroup::class)
          ->where('custom_item_priceable_id', $customItemPriceGroup->hashidToId($customItemPriceGroup->hashid))
          ->where('project_id', $project->hashidToId($project->hashid))
          ->delete();

        $customItemPriceGroup->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Custom item price group deleted'
        ], 200);
    }
}
