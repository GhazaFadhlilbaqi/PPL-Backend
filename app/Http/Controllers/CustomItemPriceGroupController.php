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
        $customItemPriceGroups = $project->customItemPriceGroup()->with('customItemPrice')->get();

        return response()->json([
            'status' => 'success',
            'data' => compact('customItemPriceGroups')
        ]);
    }

    // public function query(Project $project, Request $request)
    // {

    //     $itemPriceGroupsSearch = $project->customItemPriceGroup();

    //     if ($request->has('q') && $request->q) {
    //         $itemPriceGroupsSearch->whereHas('customItemPrice', function ($q) use ($requ) {
    //             $q->where('player_id', $playerId);
    //         })->where(function ($query) {
    //             $query->where('status', 'Pending')
    //                 ->orWhereHas('GamePlayer', function (Builder $query) {
    //                     $query->where('request_status', 'Confirm');
    //                 });
    //         })->get();
    //     }

    //     $itemPriceGroupsSearch = $itemPriceGroupsSearch->get();

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => [
    //             'itemPriceGroups' => $itemPriceGroupsSearch,
    //         ],
    //     ]);
    // }

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
        $customItemPriceGroup->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Custom item price group deleted'
        ], 200);
    }

    public function update(Project $project, CustomItemPriceGroup $customItemPriceGroup, Request $request)
    {
        $customItemPriceGroup->update($request->only([
            'name'
        ]));

        return response()->json([
            'status' => 'success',
        ]);
    }
}
