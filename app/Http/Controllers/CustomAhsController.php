<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomAhsRequest;
use App\Models\CustomAhs;
use App\Models\Project;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class CustomAhsController extends CountableItemController
{
    public function index(Project $project, Request $request)
    {
        $customAhs = CustomAhs::where('project_id', $project->hashidToId($project->hashid))->with(['customAhsItem' => function($q) {
            $q->with(['unit', 'customAhsItemable']);
        }])->get();

        if ($request->has('arrange') && $request->arrange == 'true') {

            $arrangedCustomAhs = [];

            foreach ($customAhs as $key => $cAhs) {
                foreach ($cAhs->customAhsItem as $cAhsItem) $arrangedCustomAhs[$cAhsItem->section][] = $cAhsItem;
                $customAhs[$key]['item_arranged'] = $arrangedCustomAhs;
                $arrangedCustomAhs = [];
                $customAhs[$key] = $this->countCustomAhsSubtotal($cAhs, $project->province->id);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $customAhs,
        ]);
    }

    // FIXME: Using validation request
    public function update(Project $project, CustomAhs $customAhs, Request $request)
    {

        // TODO: Implement update validation, update all child if code updated !
        $customAhs->update($request->only([
            'code', 'name'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhs')
        ]);
    }

    public function destroy(Project $project, CustomAhs $customAhs)
    {
        $customAhs->delete();
        // TODO: Implement delete AHS Item and it's relation
        return response()->json([
            'status' => 'success',
        ], 204);
    }

    // FIXME: Using validation request
    public function store(Project $project, Request $request)
    {

        // TODO: Implement validation for same project and ahs id !
        $request->merge([
            'project_id' => $project->hashidToId($project->hashid)
        ]);

        $customAhs = CustomAhs::create($request->only([
            'name', 'code', 'project_id'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('customAhs')
        ]);
    }
}
