<?php

namespace App\Http\Controllers;

use App\Models\CustomAhp;
use App\Models\CustomAhs;
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

    public function getCustomAhsItemableId(Project $project, Request $request)
    {
        $customAhsItemableId = $this->generateItemableId($project);

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhsItemableId')
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

        if ($request->has('custom_ahs_itemable_type')) {
            $request->merge(['custom_ahs_itemable_type' => 'App\\Models\\' . $request->custom_ahs_itemable_type]);
        }

        if ($request->has('unit_id')) {
            $request->merge(['unit_id' => Hashids::decode($request->unit_id)[0]]);
        }

        $customAhsItem->update($request->only([
            'name', 'unit_id', 'coefficient', 'section', 'custom_ahs_itemable_id', 'custom_ahs_itemable_type'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => 'Berhasil mengupdate item AHS'
        ]);
    }

    private function generateItemableId(Project $project)
    {
        $customItemPriceIds = CustomItemPrice::where('project_id', $project->hashidToId($project->hashid))->get()->map(function($customItemPrice) {
            return [
                'custom_ahs_itemable_type' => "App\\Models\\CustomItemPrice",
                'custom_ahs_itemable_id' => $customItemPrice->id,
                'hashed_ahs_itemable_id' => Hashids::encode($customItemPrice->id),
                'display_id' => $customItemPrice->code
            ];
        });

        # Get ahs ids
        $customAhsIds = CustomAhs::where('project_id', $project->hashidToId($project->hashid))->get()->map(function($customAhs) {
            return [
                'custom_ahs_itemable_type' => "App\\Models\\CustomAhs",
                'custom_ahs_itemable_id' => $customAhs->id,
                'hashed_ahs_itemable_id' => Hashids::encode($customAhs->id),
                'display_id' => $customAhs->code,
            ];
        });

        # Get AHP ids
        $customAhpIds = CustomAhp::where('project_id', $project->hashidToId($project->hashid))->get()->map(function($customAhp) {
            return [
                'custom_ahs_itemable_type' => 'App\\Models\\CustomAhp',
                'custom_ahs_itemable_id' => $customAhp->id,
                'hashed_ahs_itemable_id' => Hashids::encode($customAhp->id),
                'display_id' => $customAhp->code,
            ];
        });

        return ($customItemPriceIds->count()) ? $customItemPriceIds->merge($customAhsIds)->merge($customAhpIds) : [];
    }
}
