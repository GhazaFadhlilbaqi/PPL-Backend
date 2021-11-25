<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomAhsRequest;
use App\Models\CustomAhs;
use App\Models\Project;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class CustomAhsController extends Controller
{
    public function index(Project $project)
    {
        $customAhs = $project->customAhs;

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
