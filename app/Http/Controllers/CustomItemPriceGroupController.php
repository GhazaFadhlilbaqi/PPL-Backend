<?php

namespace App\Http\Controllers;

use App\Models\CustomItemPriceGroup;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomItemPriceGroupController extends Controller
{
    public function index(Project $project)
    {
        $customItemPriceGroups = $project->customItemPriceGroup;

        return response()->json([
            'status' => 'success',
            'data' => compact('customItemPriceGroups'),
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
}
