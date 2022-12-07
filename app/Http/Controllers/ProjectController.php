<?php

namespace App\Http\Controllers;

use App\Exports\ProjectRabExport;
use App\Http\Requests\ProjectRequest;
use App\Models\Order;
use App\Models\Project;
use App\Models\Province;
use Carbon\Carbon;
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
              })->addColumn('created_at_formatted', function($data) {
                  return date('d-m-Y', strtotime($data->created_at));
              })->make();
    }

    public function store(ProjectRequest $request)
    {

        $request->merge([
            'user_id' => Auth::user()->id,
            'province_id' => Province::findByHashid($request->province_id)->id,
        ]);

        $project = Project::create($request->only([
            'user_id', 'name', 'activity', 'job', 'address', 'province_id', 'fiscal_year', 'profit_margin', 'ppn'
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
            'name', 'activity', 'job', 'address', 'fiscal_year', 'profit_margin', 'province_id', 'ppn'
        ]));

        return response()->json([
            'status' => 'success',
            'data' => compact('project')
        ]);
    }

    public function updateLastOpenedAt(Project $project)
    {
        $project->last_opened_at = Carbon::now();
        $project->update();

        return response()->json([
            'status' => 'success',
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

    public function export(Project $project)
    {
        $projectId = $project->hashidToId($project->hashid);

        // In trial mode, create order when user export RAB
        if (env('APP_USER_TRIAL_MODE')) {
            $order = Order::create([
                'order_id' => generateRandomOrderId(),
                'user_id' => Auth::user()->id,
                'project_id' => $projectId,
                'gross_amount' => 0,
                'status' => 'completed'
            ]);
        }

        // FIXME: SECURITY HOLE ! if somemone unauthorized access this route with knowing project id, then the user might lost his order to export
        $order = Order::where('project_id', $projectId)->where('status', 'completed')->where('used_at', null)->first();

        if ($order) {
            $order->used_at = Carbon::now();
            $order->save();
            return (new ProjectRabExport($projectId))->download('exports.xlsx');
        } else {
            return abort(403);
        }

    }

    private function giveUnbelongedAccessResponse()
    {
        return response()->json([
            'status' => 'fail',
            'message' => 'Nice try ! this project ID isn\'t belongs to current user'
        ], 400);
    }
}
