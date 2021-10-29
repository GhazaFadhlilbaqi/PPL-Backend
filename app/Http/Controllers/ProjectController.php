<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{

    public function index(Request $request)
    {
        $projects = Auth::user()->project;

        return response()->json([
            'status' => 'success',
            'data' => compact('projects')
        ]);
    }

    public function store(ProjectRequest $request)
    {

        $request->merge(['user_id' => Auth::user()->id]);

        $project = Project::create($request->only([
            'user_id', 'name', 'activity', 'job', 'address', 'province_id', 'fiscal_year', 'profit_margin'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('project')
        ]);
    }

    public function update(Project $project, ProjectRequest $request)
    {

        if ($project->user_id != Auth::user()->id) return $this->giveUnbelongedAccessResponse();

        $project->update($request->only([
            'name', 'activity', 'job', 'address', 'province_id', 'fiscal_year', 'profit_margin'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('project')
        ]);
    }

    public function destroy(Project $project)
    {
        if ($project->user_id != Auth::user()->id) return $this->giveUnbelongedAccessResponse();

        $project->delete();

        return response()->json([
            'status' => 'success',
        ], 204);
    }

    private function giveUnbelongedAccessResponse()
    {
        return response()->json([
            'status' => 'fail',
            'message' => 'Nice try ! this project ID isn\'t belongs to current user'
        ], 400);
    }
}
