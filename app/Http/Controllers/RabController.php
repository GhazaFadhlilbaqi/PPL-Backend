<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rab;
use Illuminate\Http\Request;

class RabController extends Controller
{

    public function index(Request $request, Project $project)
    {
        $rabs = Rab::where('project_id', $project->hashidToId($project->hashid))->get();

        return response()->json([
            'status' => 'success',
            'data' => compact('rabs')
        ]);
    }

    public function store(Request $request, Project $project)
    {

        $request->merge(['project_id' => $project->hashidToId($project->hashid)]);
        $rab = Rab::create($request->only(['name', 'project_id']));

        return response()->json([
            'status' => 'success',
            'data' => compact('rab')
        ]);
    }

    public function update(Request $request, Project $project, Rab $rab)
    {
        $rab->update($request->only(['name']));

        return response()->json([
            'status' => 'success',
            'data' => compact('rab'),
        ]);
    }

    public function destroy(Project $project, Rab $rab)
    {

        $rab->delete();

        return response()->json([
            'status' => 'success',
        ], 204);
    }
}
