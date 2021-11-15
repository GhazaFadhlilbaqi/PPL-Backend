<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{

    public function index(Request $request)
    {
        return $this->getTableFormattedData(
            Project::where('user_id', Auth::user()->id)->with('province'))
            ->addColumn('last_opened_at_formatted', function($data) {
                return $data->last_opened_at ? date('d-m-Y', strtotime($data->last_opened_at)) : 'Belum Pernah di Buka';
            })
            ->addColumn('created_at_formatted', function($data) {
                return date('d-m-Y', strtotime($data->created_at));
            })
            ->make();
    }

    public function store(ProjectRequest $request)
    {

        $request->merge([
            'user_id' => Auth::user()->id,
            'province_id' => Province::findByHashid($request->province_id)->id,
        ]);

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

        $request->merge(['province_id' => Province::findByHashid($request->province_id)->id]);

        $project->update($request->only([
            'name', 'activity', 'job', 'address', 'fiscal_year', 'profit_margin', 'province_id'
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

    public function show(Project $project)
    {
        return response()->json([
            'status' => 'success',
            'data' => compact('project')
        ]);
    }

    private function giveUnbelongedAccessResponse()
    {
        return response()->json([
            'status' => 'fail',
            'message' => 'Nice try ! this project ID isn\'t belongs to current user'
        ], 400);
    }
}
